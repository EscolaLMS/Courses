<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Http\Requests\Abstracts\TopicResourceAPIRequest;

class DeleteTopicResourceAPIRequest extends TopicResourceAPIRequest
{
    public function getTopicResourceId(): int
    {
        return $this->route('resource_id');
    }

    public function rules(): array
    {
        return [];
    }
}
