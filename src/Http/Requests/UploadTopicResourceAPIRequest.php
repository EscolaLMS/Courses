<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Http\Requests\Abstracts\TopicResourceAPIRequest;
use Illuminate\Http\UploadedFile;

class UploadTopicResourceAPIRequest extends TopicResourceAPIRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'resource' => ['required', 'file'],
        ]);
    }

    public function getUploadedResource(): UploadedFile
    {
        return $this->file('resource');
    }
}
