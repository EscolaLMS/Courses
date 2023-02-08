<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use Illuminate\Foundation\Http\FormRequest;

class CreateLessonAPIRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        $course = Course::find($this->input('course_id'));
        if ($course) {
            return $user->can('update', $course);
        }

        $parentLesson = Lesson::find($this->input('parent_lesson_id'));

        if ($parentLesson) {
            return $user->can('update', $parentLesson);
        }

        return false;
    }

    public function rules(): array
    {
        return Lesson::$rules;
    }
}
