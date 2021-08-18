<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Models\Lesson;
use Illuminate\Foundation\Http\FormRequest;

class GetLessonAPIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = auth()->user();
        $lesson = $this->getLesson();
        if (is_null($lesson)) {
            return true; // controller will fire 404 error
        }
        $course = $lesson->course;
        return isset($user) ? $user->can('attend', $course) : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    public function getLesson(): ?Lesson
    {
        return Lesson::find($this->route('lesson'));
    }

    public function userIsUnprivileged(): bool
    {
        return empty($this->user()) || $this->user()->cannot('update', $this->getLesson()->course);
    }
}
