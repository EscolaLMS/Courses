<?php


namespace EscolaLms\Courses\Tests;


use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Enum\ProgressStatus;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;

trait ProgressConfigurable
{
    /**
     * @param Course $course
     * @param int $status
     * @return array
     */
    private function getProgressUpdate(Course $course, int $status = ProgressStatus::COMPLETE): array
    {
        $progress = [];
        foreach ($course->lessons as $lesson) {
            foreach ($lesson->topics as $topic) {
                $progress[] = [
                    'topic_id' => $topic->getKey(),
                    'status' => $status
                ];
            }
        }
        return $progress;
    }

    private function progressUpdate(Course $course, User $user, int $status = ProgressStatus::COMPLETE): CourseProgressCollection
    {
        return CourseProgressCollection::make($user, $course)
            ->start()
            ->setProgress($this->getProgressUpdate($course, $status));
    }

}