<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;

use Illuminate\Foundation\Http\FormRequest;

class DeleteLessonAPIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = auth()->user();
        $lesson = Lesson::find($this->route('lesson'));
        return isset($user) && $user->can('delete', $lesson);
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
}
