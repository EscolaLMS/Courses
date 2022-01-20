<?php

namespace EscolaLms\Courses\Http\Resources;

use EscolaLms\Auth\Traits\ResourceExtandable;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonSimpleResource extends LessonResource
{
    public function toArray($request)
    {
        return parent::toArray($request) +
            [
                'topics' => TopicSimpleResource::collection($this->topics)
            ];
    }
}
