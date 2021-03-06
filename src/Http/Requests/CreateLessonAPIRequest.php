<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Course;
use Illuminate\Foundation\Http\FormRequest;

class CreateLessonAPIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = auth()->user();
        $course = Course::find($this->input('course_id'));
        return isset($user) ? $user->can('update', $course) : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return Lesson::$rules;
    }
}
