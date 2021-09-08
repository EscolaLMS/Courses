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
        $topic = Topic::find($this->route('topic'));
        if (!isset($topic)) {
            return true; // controller will fire 404 error
        }
        return isset($user) && $user->can('delete', $topic);
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
