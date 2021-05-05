<?php


namespace EscolaLms\Courses\ValueObjects;


use Carbon\Carbon;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\ValueObjects\Contracts\CourseProgressCollectionContract;
use EscolaLms\Courses\ValueObjects\Contracts\ValueObjectContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

class CourseProgressCollection extends ValueObject implements ValueObjectContract, CourseProgressCollectionContract
{
    private Authenticatable $user;
    private Course $course;
    private Collection $progress;
    private int $totalSpentTime;
    private ?Carbon $finishDate;

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
        foreach ($existingProgresses as $record) {
            $progress->push([
                'status' => $record->status
            ]);
            $this->totalSpentTime += $record->seconds;

            if (is_null($this->finishDate) || $this->finishDate <= $record->finished_at) {
                $this->finishDate = $record->finished_at;
            }
        }

        return $progress->values();
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