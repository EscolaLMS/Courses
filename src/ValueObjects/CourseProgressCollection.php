<?php

namespace EscolaLms\Courses\ValueObjects;

use Carbon\Carbon;
use EscolaLms\Courses\Enum\ProgressStatus;
use EscolaLms\Courses\Events\CourseAssigned;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\CourseProgress;
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

    private int $totalSpentTime;
    private ?Carbon $finishDate;

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
        $this->finishDate = null;
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
        $topicWithoutProgressId = CourseProgress::where('user_id', $this->user->getKey())->whereIn('topic_id', $this->topics->toArray())->pluck('topic_id')->toArray();
        $topicsWithoutProgress = $this->course
            ->topics()
            ->whereNotIn(
                'topics.id',
                $topicWithoutProgressId
            )->where('topics.active', true)
            ->get(['topics.id']);
        foreach ($topicsWithoutProgress as $topic) {
            $this->courseProgressRepositoryContract->updateInTopic($topic, $this->user, ProgressStatus::INCOMPLETE);
        }

        /** @var EloquentCollection $courseProgresses */
        $courseProgresses = CourseProgress::where('user_id', $this->user->getKey())->whereIn('topic_id', $this->topics->toArray())->get(['topic_id', 'status', 'seconds', 'finished_at']);

        $this->totalSpentTime = $courseProgresses->sum('seconds');
        $this->finishDate = $courseProgresses->max('finished_at');

        return $courseProgresses->sortBy('topic_id')->values();
    }

    public function ping(Topic $topic): self
    {
        $secondsPassed = 0;

        $progress = $this->courseProgressRepositoryContract->findProgress($topic, $this->user);
        if ($progress->status === ProgressStatus::COMPLETE) {
            throw new RuntimeException("Topic is already finished.");
        }
        $secondsPassed = (int) $progress->seconds;

        $lastTrack = $this->courseProgressRepositoryContract->getUserLastTimeInTopic($this->user, $topic);

        if ($this->hasActiveProgressSession($lastTrack)) {
            $secondsDiff = $lastTrack->diffInSeconds(Carbon::now());
            $secondsPassed += $secondsDiff;
            $this->courseProgressRepositoryContract->updateInTopic($topic, $this->user, ProgressStatus::IN_PROGRESS, $secondsPassed);
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
        return $this->progress->whereNotIn('status', [ProgressStatus::COMPLETE])->count() == 0;
    }

    public function getProgress(): EloquentCollection
    {
        return $this->progress;
    }

    public function setProgress(array $progress): CourseProgressCollectionContract
    {
        foreach ($progress as $topicProgress) {
            $topic = Topic::findOrFail($topicProgress['topic_id']);

            $this->courseProgressRepositoryContract->updateInTopic(
                $topic,
                $this->user,
                $topicProgress['status']
            );
        }

        $this->progress = $this->buildProgress();

        return $this;
    }

    public function getTotalSpentTime(): int
    {
        return $this->totalSpentTime;
    }

    public function getFinishDate(): ?Carbon
    {
        return $this->finishDate;
    }

    public function toArray(): array
    {
        return $this->getProgress()->toArray();
    }
}
