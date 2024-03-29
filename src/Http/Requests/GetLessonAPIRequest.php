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
        return isset($user) && $user->can('view', $lesson);
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
}
