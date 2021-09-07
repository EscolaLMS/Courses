<?php

namespace EscolaLms\Courses\Http\Requests;

class DeleteTopicResourceAPIRequest extends TopicResourceAPIRequest
{
    public function getTopicResourceId(): int
    {
        return $this->route('resource_id');
    }
}
