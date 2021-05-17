<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Models\Topic;
use Illuminate\Foundation\Http\FormRequest;

class GetTopicAPIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = auth()->user();
        $topic = Topic::find($this->route('topic'));
        if (!isset($topic)) {
            return true; // controller will fire 404 error
        }
        $course = $topic->lesson->course;
        return isset($user) ? $user->can('attend', $course) : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [ ];
    }
}
