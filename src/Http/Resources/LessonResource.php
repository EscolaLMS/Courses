<?php

namespace EscolaLms\Courses\Http\Resources;

use EscolaLms\Courses\Models\Lesson;
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
            'lessons' => LessonSimpleResource::collection($this->lessons->filter(fn (Lesson $lesson) => $lesson->active)->sortBy('order')),
        ];
    }
}
