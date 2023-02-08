<?php

namespace EscolaLms\Courses\Http\Resources;

class LessonSimpleResource extends LessonResource
{
    public function toArray($request): array
    {
        return parent::toArray($request) +
            [
                'topics' => TopicSimpleResource::collection($this->topics),
            ];
    }
}
