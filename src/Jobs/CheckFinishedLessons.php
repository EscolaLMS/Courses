<?php

namespace EscolaLms\Courses\Jobs;

use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Courses\Enum\ProgressStatus;
use EscolaLms\Courses\Events\LessonFinished;
use EscolaLms\Courses\Models\CourseProgress;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Repositories\Contracts\TopicRepositoryContract;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CheckFinishedLessons implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $topicId;
    public int $userId;

    public function __construct(int $topicId, int $userId)
    {
        $this->topicId = $topicId;
        $this->userId = $userId;
    }

    public function handle(TopicRepositoryContract $topicRepository, UserRepositoryContract $userRepository): void
    {
        $topic = $topicRepository->find($this->topicId);
        $user = $userRepository->find($this->userId);

        if (!$user || !$topic || !$topic->course) {
            return;
        }

        $courseProgressCollection = CourseProgressCollection::make($user, $topic->course);
        $finishedTopicIds = $courseProgressCollection
            ->getProgress()
            ->filter(fn(CourseProgress $courseProgress) => $courseProgress->finished_at && $courseProgress->status === ProgressStatus::COMPLETE)
            ->pluck('topic_id');

        $this->checkLesson($topic->lesson, $finishedTopicIds, $user);
    }

    private function checkLesson(Lesson $lesson, Collection $finishedTopicIds, $user): void
    {
        $topicIds = $this->getAllNestedTopicIds($lesson);

        if ($topicIds->diff($finishedTopicIds)->isEmpty()) {
            event(new LessonFinished($user, $lesson));

            if ($lesson->parentLesson && $lesson->parentLesson->active) {
                $this->checkLesson($lesson->parentLesson, $finishedTopicIds, $user);
            }
        }
    }

    private function getAllNestedTopicIds(Lesson $lesson): Collection
    {
        $topicIds = $lesson->topics->where('active', true)->pluck('id');

        foreach ($lesson->lessons as $childLesson) {
            $topicIds->merge($this->getAllNestedTopicIds($childLesson));
        }

        return $topicIds;
    }
}
