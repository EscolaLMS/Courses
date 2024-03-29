<?php

namespace EscolaLms\Courses\Rules;

use EscolaLms\Courses\Models\Lesson;
use Illuminate\Contracts\Validation\Rule;

class ValidParentLesson implements Rule
{
    private ?int $courseId;

    public function __construct(int $courseId = null)
    {
        $this->courseId = $courseId;
    }

    public function passes($attribute, $value): bool
    {
        if (!is_numeric($this->courseId)) {
            return false;
        }

        $parentLesson = Lesson::find($value);

        if (!$parentLesson || $parentLesson->course_id !== $this->courseId) {
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return __('The parent lesson must be in the course');
    }
}
