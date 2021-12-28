<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Models\Topic;
use Illuminate\Foundation\Http\FormRequest;

class CloneTopicAPIRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        $topic = $this->getTopic();

        if (is_null($topic)) {
            return true; // controller will fire 404 error
        }

        return isset($user) && $user->can('clone', $topic);
    }

    public function rules(): array
    {
        return [];
    }

    public function getTopic(): ?Topic
    {
        return Topic::find($this->route('id'));
    }
}
