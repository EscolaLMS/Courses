<?php


namespace EscolaLms\Courses\ValueObjects;


use Carbon\Carbon;
use EscolaLms\Courses\Enum\ProgressStatus;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Repositories\Contracts\CourseProgressRepositoryContract;
use EscolaLms\Courses\ValueObjects\Contracts\CourseProgressCollectionContract;
use EscolaLms\Courses\ValueObjects\Contracts\ValueObjectContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use RuntimeException;

class CourseProgressCollection extends ValueObject implements ValueObjectContract, CourseProgressCollectionContract
{
    public const FORGET_TRACKING_SESSION_AFTER_MINUTES = 60;

    private Authenticatable $user;
    private Course $course;
    private Collection $progress;
    private int $totalSpentTime;
    private ?Carbon $finishDate;
    private CourseProgressRepositoryContract $courseProgressRepositoryContract;

    public function __construct(
        CourseProgressRepositoryContract $courseProgressRepositoryContract
    )
    {
        $this->courseProgressRepositoryContract = $courseProgressRepositoryContract;
    }

    public function build(Authenticatable $user, Course $course): self
    {
        $this->user = $user;
        $this->course = $course;
        $this->totalSpentTime = 0;
        $this->finishDate = null;
        $this->progress = $this->buildProgress();

        return $this;
    }

    private function buildProgress(): Collection
    {
        $progress = new Collection();
        $existingProgresses = ($this->course->progress()->where('user_id', $this->user->getKey())->get());
        $topicsWithoutProgress = $this->course
            ->topic()
            ->whereNotIn(
                'topics.id',
                $existingProgresses->pluck('topic_id')
            )->get();

        foreach ($existingProgresses as $record) {
            $progress->push([
                'status' => $record->status
            ]);
            $this->totalSpentTime += $record->seconds;

            if (is_null($this->finishDate) || $this->finishDate <= $record->finished_at) {
                $this->finishDate = $record->finished_at;
            }
        }

        foreach ($topicsWithoutProgress as $record) {
            $progress->push([
                'topic_id' => $record->getKey(),
                'status' => ProgressStatus::INCOMPLETE
            ]);
        }

        return $progress->sortBy('topic_id')->values();
    }

    public function ping(Topic $topic): self
    {
        $progress = $this->courseProgressRepositoryContract->findProgress($topic, $this->user);

        if (($progress->status ?? ProgressStatus::INCOMPLETE) == ProgressStatus::COMPLETE) {
            throw new RuntimeException("Lecture is already finished.");
        }

        $secondsPassed = $progress->seconds ?? 0;

        $lastTrack = $this->courseProgressRepositoryContract->getUserLastTimeInTopic($this->user, $topic);

        $now = Carbon::now();

        if ($this->hasActiveProgressSession($lastTrack)) {
            $secondsDiff = $lastTrack->diffInSeconds($now);
            $secondsPassed += $secondsDiff;
            $this->courseProgressRepositoryContract->updateInTopic($topic, $this->user, ProgressStatus::IN_PROGRESS, $secondsPassed);
        }

        $this->courseProgressRepositoryContract->updateUserTimeInTopic($this->user, $topic);

        return $this;
    }

    /**
     * @param Carbon|null $lastTrack
     * @return bool
     */
    private function hasActiveProgressSession(?Carbon $lastTrack): bool
    {
        return !(is_null($lastTrack) || $lastTrack->lte(Carbon::now()->subMinutes(self::FORGET_TRACKING_SESSION_AFTER_MINUTES)));
    }



    public function getUser(): Authenticatable
    {
        // TODO: Implement getUser() method.
    }

    public function getCourse(): Course
    {
        // TODO: Implement getCourse() method.
    }

    public function start(): CourseProgressCollectionContract
    {
        // TODO: Implement start() method.
    }

    public function isStarted(): bool
    {
        // TODO: Implement isStarted() method.
    }

    public function isFinished(): bool
    {
        // TODO: Implement isFinished() method.
    }

    public function getProgress(): Collection
    {
        return $this->progress;
    }

    public function setProgress(array $progress): CourseProgressCollectionContract
    {
        // TODO: Implement setProgress() method.
    }

    public function getTotalSpentTime(): int
    {
        // TODO: Implement getTotalSpentTime() method.
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