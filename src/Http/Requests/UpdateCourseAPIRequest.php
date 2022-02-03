<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Rules\ValidAuthor;
use EscolaLms\Files\Rules\FileOrStringRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseAPIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = auth()->user();
        $course = Course::find($this->route('course'));
        return isset($user) ? $user->can('update', $course) : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $prefixPath = 'course/' . $this->route('course');

        return array_merge(Course::rules(),
            [
            'authors.*' => ['numeric', new ValidAuthor()],
            'image' => [new FileOrStringRule(['image'], $prefixPath)],
            'video' => [new FileOrStringRule(['mimes:mp4,ogg,webm'], $prefixPath)],
            'poster' => [new FileOrStringRule(['image'], $prefixPath)],
        ]);
    }
}
