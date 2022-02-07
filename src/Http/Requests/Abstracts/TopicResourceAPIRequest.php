<?php

namespace EscolaLms\Courses\Http\Requests\Abstracts;

use EscolaLms\Courses\Models\Topic;
use Illuminate\Foundation\Http\FormRequest;

abstract class TopicResourceAPIRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return !empty($user) && $user->can('update', $this->getTopic());
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['topic_id' => $this->route('topic_id')]);
    }

    public function getTopic(): Topic
    {
        return Topic::findOrFail($this->route('topic_id'));
    }

    public function rules(): array
    {
        return ['topic_id' => ['required', 'exists:' . (new Topic())->getTable() . ',id']];
    }
}
