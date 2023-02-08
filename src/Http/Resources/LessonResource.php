<?php

namespace EscolaLms\Courses\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
            'children_lessons' => LessonSimpleResource::collection($this->childrenLessons),
        ];
    }
}
