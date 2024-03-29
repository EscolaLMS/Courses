<?php

namespace EscolaLms\Courses\ValueObjects;

use Carbon\Carbon;
use EscolaLms\Courses\Enum\ProgressStatus;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\CourseProgress;
use EscolaLms\Courses\Models\CourseUserPivot;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Repositories\Contracts\CourseProgressRepositoryContract;
use EscolaLms\Courses\ValueObjects\Contracts\CourseProgressCollectionContract;
use EscolaLms\Courses\ValueObjects\Contracts\ValueObjectContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use RuntimeException;

class CourseProgressCollection extends ValueObject implements ValueObjectContract, CourseProgressCollectionContract
{
    public const FORGET_TRACKING_SESSION_AFTER_MINUTES = 60;

    private CourseProgressRepositoryContract $courseProgressRepositoryContract;

    private Authenticatable $user;
    private Course $course;

    private Collection $topics;
    private EloquentCollection $progress;

    private ?CourseUserPivot $pivot;

    private int $totalSpentTime;
    private ?Carbon $startDate;
    private ?Carbon $finishDate;
    private ?Carbon $deadline;
    private ?Carbon $endDate;

    public function __construct(
        CourseProgressRepositoryContract $courseProgressRepositoryContract
    ) {
        $this->courseProgressRepositoryContract = $courseProgressRepositoryContract;
    }

    public function build(Authenticatable $user, Course $course): self
    {
        $this->user = $user;
        $this->course = $course;
        $this->totalSpentTime = 0;
        $this->startDate = null;
        $this->finishDate = null;
        $this->pivot = CourseUserPivot::query()->where('user_id', $user->getKey())->where('course_id', $course->getKey())->first();
        $this->deadline = $this->pivot ? $this->pivot->deadline : null;
        $this->endDate = $this->pivot ? $this->pivot->end_date : null;
        $this->topics = $this->getActiveTopicIdsFromCourses();
        $this->progress = $this->buildProgress();

        return $this;
    }

    private function getActiveTopicIdsFromCourses(): Collection
    {
        return $this->course->topics->where('active', true)->pluck('id');
    }

    private function buildProgress(): EloquentCollection
    {
        $topicWithProgressId = CourseProgress::where('user_id', $this->user->getKey())->whereIn('topic_id', $this->topics->toArray())->pluck('topic_id')->toArray();
        $topicsWithoutProgress = $this->course
            ->topics()
            ->whereNotIn(
                'topics.id',
                $topicWithProgressId
            )->where('topics.active', true)
            ->get(['topics.id']);
        foreach ($topicsWithoutProgress as $topic) {
            $this->courseProgressRepositoryContract->updateInTopic($topic, $this->user, ProgressStatus::INCOMPLETE);
        }

        /** @var EloquentCollection $courseProgresses */
        $courseProgresses = CourseProgress::where('user_id', $this->user->getKey())->whereIn('topic_id', $this->topics->toArray())->get(['topic_id', 'status', 'seconds', 'started_at', 'finished_at']);

        $this->totalSpentTime = $courseProgresses->sum('seconds');
        $this->startDate = $courseProgresses->min('started_at');
        $this->finishDate = $courseProgresses->max('finished_at');
        $this->deadline = null;

        if (!is_null($this->course->hours_to_complete) && !is_null($this->startDate)) {
            $this->deadline = $this->startDate->addHours($this->course->hours_to_complete);
        }

        if (!is_null($this->course->active_to) && (is_null($this->deadline) || $this->course->active_to->lessThan($this->deadline))) {
            $this->deadline = $this->course->active_to;
        }

        if (!is_null($this->pivot)) {
            $this->pivot->deadline = $this->deadline;
            $this->pivot->save();
        }

        return $courseProgresses->sortBy('topic_id')->values();
    }

    public function ping(Topic $topic): self
    {
        if (!$this->topicCanBeProgressed($topic)) {
            return $this;
        }
        $progress = $this->courseProgressRepositoryContract->findProgress($topic, $this->user);

        $secondsPassed = $progress->seconds;

        $lastTrack = $this->courseProgressRepositoryContract->getUserLastTimeInTopic($this->user, $topic);

        if ($this->hasActiveProgressSession($lastTrack)) {
            $secondsDiff = $lastTrack->diffInSeconds(Carbon::now());
            $secondsPassed += $secondsDiff;
            $this->courseProgressRepositoryContract
                ->updateInTopic($topic, $this->user, $progress->status === ProgressStatus::COMPLETE ? ProgressStatus::COMPLETE : ProgressStatus::IN_PROGRESS, $secondsPassed);
        }

        $this->courseProgressRepositoryContract->updateUserTimeInTopic($this->user, $topic);

        return $this;
    }

    private function hasActiveProgressSession(?Carbon $lastTrack): bool
    {
        return !(is_null($lastTrack) || $lastTrack->lte(Carbon::now()->subMinutes(self::FORGET_TRACKING_SESSION_AFTER_MINUTES)));
    }

    public function getUser(): Authenticatable
    {
        return $this->user;
    }

    public function getCourse(): Course
    {
        return $this->course;
    }

    public function isFinished(): bool
    {
        return $this->countNotFinishedTopics() === 0
            && $this->countFinishedTopics() >= $this->course->topics()->where('topics.active', true)->count();
    }

    public function countFinishedTopics(): int
    {
        return $this->progress->where('status', ProgressStatus::COMPLETE)->count();
    }

    public function countNotFinishedTopics(): int
    {
        return $this->progress->whereNotInStrict('status', [ProgressStatus::COMPLETE])->count();
    }

    public function getProgress(): EloquentCollection
    {
        return $this->progress;
    }

    public function setProgress(array $progress): CourseProgressCollectionContract
    {
        if (!$this->courseCanBeProgressed()) {
            return $this;
        }

        $incomplete = array_filter($progress, fn ($item) => $item['status'] === ProgressStatus::INCOMPLETE);
        $newAttempt = count($incomplete) === $this->topics->count();
        foreach ($progress as $topicProgress) {
            $topic = Topic::findOrFail($topicProgress['topic_id']);

            if ($this->topicCanBeProgressed($topic)) {
                $this->courseProgressRepositoryContract->updateInTopic(
                    $topic,
                    $this->user,
                    $topicProgress['status'],
                    null,
                    $newAttempt
                );
            }
        }

        $this->progress = $this->buildProgress();

        return $this;
    }

    public function getTotalSpentTime(): int
    {
        return $this->totalSpentTime;
    }

    public function getStartDate(): ?Carbon
    {
        return $this->startDate;
    }

    public function getFinishDate(): ?Carbon
    {
        return $this->finishDate;
    }

    public function getDeadline(): ?Carbon
    {
        return $this->deadline;
    }

    public function getEndDate(): ?Carbon
    {
        return $this->endDate;
    }

    public function afterDeadline(): bool
    {
        return $this->getDeadline() && Carbon::now()->greaterThanOrEqualTo($this->getDeadline());
    }

    public function afterEndDate(): bool
    {
        return $this->getEndDate() && Carbon::now()->greaterThanOrEqualTo($this->getEndDate());
    }

    public function toArray(): array
    {
        return $this->getProgress()->toArray();
    }

    public function topicCanBeProgressed(Topic $topic): bool
    {
        return $this->courseCanBeProgressed() && $topic->active && !$this->afterEndDate();
    }

    public function courseCanBeProgressed(): bool
    {
        return $this->course->is_active && !$this->afterDeadline();
    }
}
