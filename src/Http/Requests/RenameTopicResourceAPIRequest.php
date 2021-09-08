<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Http\Requests\Abstracts\TopicResourceAPIRequest;

class RenameTopicResourceAPIRequest extends TopicResourceAPIRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'name' => ['required', 'string'],
        ]);
    }

    public function getTopicResourceId(): int
    {
        return $this->route('resource_id');
    }

    public function getName(): string
    {
        return $this->input('name');
    }
}
