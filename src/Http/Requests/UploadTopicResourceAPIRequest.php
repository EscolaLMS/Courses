<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Http\Requests\Abstracts\TopicResourceAPIRequest;
use EscolaLms\Courses\Rules\TopicResourceRule;

class UploadTopicResourceAPIRequest extends TopicResourceAPIRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'resource' => ['required', new TopicResourceRule($this->route('topic_id'))],
        ]);
    }

    public function getUploadedResource()
    {
        if ($this->hasfile('resource')) {
            return $this->file('resource');
        }

        return $this->get('resource');
    }
}
