<?php

namespace EscolaLms\Courses\Http\Requests\Abstracts;

use EscolaLms\Courses\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class CourseAccessAPIRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return !empty($user) && $user->can('update', $this->getCourse());
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['course_id' => $this->route('course_id')]);
    }

    public function getCourse(): Course
    {
        return Course::find($this->route('course_id'));
    }

    public function rules(): array
    {
        return ['course_id' => ['required', Rule::exists((new Course())->getTable(), 'id')]];
    }
}
