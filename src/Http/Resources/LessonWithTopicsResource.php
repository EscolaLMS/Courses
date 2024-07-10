<?php

namespace EscolaLms\Courses\Http\Resources;

use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\ModelFields\Enum\MetaFieldVisibilityEnum;
use EscolaLms\ModelFields\Facades\ModelFields;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonWithTopicsResource extends JsonResource
{
    public function __construct(Lesson $resource)
    {
        parent::__construct($resource);
    }

    public function getResource(): Lesson
    {
        return $this->resource;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        $lesson = $this->getResource();

        $topics = $lesson->topics->filter(fn (Topic $topic) => $topic->active)->sortBy('order');
        $lessons = $lesson->lessons->filter(fn (Lesson $lesson) => $lesson->active)->sortBy('order');

        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'summary' => $this->resource->summary,
            'duration' => $this->resource->duration,
            'active' => $this->resource->active,
            'topics' => TopicResource::collection($topics),
            'order' => $this->resource->order,
            'active_from' => $this->resource->active_from,
            'active_to' => $this->resource->active_to,
            'lessons' => LessonWithTopicsResource::collection($lessons),
            ...ModelFields::getExtraAttributesValues($this->resource, MetaFieldVisibilityEnum::PUBLIC)
        ];
    }
}
