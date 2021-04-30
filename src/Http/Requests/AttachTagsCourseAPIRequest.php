<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Models\Course;
use Illuminate\Foundation\Http\FormRequest;

class AttachTagsCourseAPIRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'tags' => ['required', 'array']
        ];
    }
}
