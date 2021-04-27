<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Models\Lesson;
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
        return true;
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
