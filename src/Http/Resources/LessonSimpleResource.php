<?php

namespace EscolaLms\Courses\Http\Resources;

use EscolaLms\Courses\Models\Topic;

class LessonSimpleResource extends LessonResource
{
    public function toArray($request): array
    {
        return parent::toArray($request) +
            [
                'topics' => TopicSimpleResource::collection($this->topics->filter(fn (Topic $topic) => $topic->active)->sortBy('order')),
            ];
    }
}
