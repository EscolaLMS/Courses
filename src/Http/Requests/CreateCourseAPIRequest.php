<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Models\Course;
use Illuminate\Foundation\Http\FormRequest;

class CreateCourseAPIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = auth()->user();
        return isset($user) ? $user->can('create', Course::class) : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(Course::$rules, ['title' => ['required', 'string', "min:3"]]);
    }
}
