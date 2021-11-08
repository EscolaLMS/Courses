<?php

namespace EscolaLms\Courses\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use EscolaLms\Courses\Models\Topic;

class DeleteTopicAPIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = auth()->user();
        $topic = $this->getTopic();
        return is_null($topic) || (isset($user) && $user->can('delete', $topic));
    }

    public function getTopic(): ?null
    {
        return Topic::find($this->route('topic'));
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
