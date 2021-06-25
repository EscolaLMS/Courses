<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Models\Topic;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTopicAPIRequest extends FormRequest
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
        $course = $topic->lesson->course;
        return isset($user) ? $user->can('update', $course) : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'string|max:255',
            'lesson_id' => 'integer|exists:lessons,id',
            'active' => 'boolean',
            'preview' => 'boolean',
            'topicable_id' => 'integer',
            'topicable_type' => 'string|max:255',
            'order' => 'integer',
        ];
    }
}
