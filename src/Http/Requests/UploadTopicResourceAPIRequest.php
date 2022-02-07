<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Http\Requests\Abstracts\TopicResourceAPIRequest;
use EscolaLms\Files\Rules\FileOrStringRule;

class UploadTopicResourceAPIRequest extends TopicResourceAPIRequest
{
    public function rules(): array
    {
        $prefixPath = 'course/' . $this->getTopic()->course->getKey();

        return array_merge(parent::rules(), [
            'resource' => ['required', new FileOrStringRule(['file'], $prefixPath)],
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
