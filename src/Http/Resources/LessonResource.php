<?php

namespace EscolaLms\Courses\Http\Resources;

use EscolaLms\Courses\Models\Lesson;
use EscolaLms\ModelFields\Enum\MetaFieldVisibilityEnum;
use EscolaLms\ModelFields\Facades\ModelFields;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Lesson
 */
class LessonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'duration' => $this->duration,
            'active' => $this->active,
            'order' => $this->order,
            'course_id' => $this->course_id,
            'active_from' => $this->active_from,
            'active_to' => $this->active_to,
            'lessons' => LessonSimpleResource::collection($this->lessons->filter(fn (Lesson $lesson) => $lesson->active)->sortBy('order')),
            ...ModelFields::getExtraAttributesValues($this->resource, MetaFieldVisibilityEnum::PUBLIC)
        ];
    }
}
