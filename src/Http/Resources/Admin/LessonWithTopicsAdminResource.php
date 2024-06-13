<?php

namespace EscolaLms\Courses\Http\Resources\Admin;

use EscolaLms\Courses\Models\Lesson;
use EscolaLms\ModelFields\Enum\MetaFieldVisibilityEnum;
use EscolaLms\ModelFields\Facades\ModelFields;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Lesson
 */
class LessonWithTopicsAdminResource extends JsonResource
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
     * @param \Illuminate\Http\Request $request
     */
    public function toArray($request): array
    {
        $lesson = $this->getResource();

        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'summary' => $this->resource->summary,
            'duration' => $this->resource->duration,
            'active' => $this->resource->active,
            'topics' => TopicAdminResource::collection($lesson->topics->sortBy('order')),
            'topics_count' => $lesson->topics->count(),
            'order' => $this->resource->order,
            'lessons' => LessonWithTopicsAdminResource::collection($lesson->lessons->sortBy('order')),
            'active_from' => $this->resource->active_from,
            'active_to' => $this->resource->active_to,
            ...ModelFields::getExtraAttributesValues($this->resource, MetaFieldVisibilityEnum::PUBLIC)
        ];
    }
}
