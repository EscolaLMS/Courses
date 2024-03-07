<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Rules\ValidParentLesson;
use EscolaLms\ModelFields\Facades\ModelFields;
use Illuminate\Foundation\Http\FormRequest;

class CreateLessonAPIRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        $course = Course::find($this->input('course_id'));

        return isset($user) ? $user->can('update', $course) : false;
    }

    public function rules(): array
    {
        return array_merge(Lesson::$rules, [
            'parent_lesson_id' => ['nullable', new ValidParentLesson($this->get('course_id'))],
        ], ModelFields::getFieldsMetadataRules(Lesson::class));
    }
}
