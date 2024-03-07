<?php

namespace EscolaLms\Courses\Observers;

use EscolaLms\Courses\Models\Lesson;
use Illuminate\Database\Eloquent\Builder;

class LessonObserver
{
    public function creating(Lesson $lesson): void
    {
        if (!$lesson->order) {
            $lesson->order = 1 +
                (int)Lesson::query()
                    ->when($lesson->course_id, function (Builder $query, int $courseId) {
                        $query->where('course_id', $courseId);
                    })
                    ->when($lesson->parent_lesson_id, function (Builder $query, int $parentId) {
                        $query->where('parent_lesson_id', $parentId);
                    })
                    ->max('order');
        }
    }
}
