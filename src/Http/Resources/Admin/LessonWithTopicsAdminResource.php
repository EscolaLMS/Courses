<?php

namespace EscolaLms\Courses\Http\Resources\Admin;

use EscolaLms\Courses\Models\Lesson;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'id' => $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'duration' => $this->duration,
            'active' => $this->active,
            'topics' => TopicAdminResource::collection($lesson->topics->sortBy('order')),
            'order' => $this->order,
            'lessons' => LessonWithTopicsAdminResource::collection($lesson->lessons->sortBy('order')),
        ];
    }
}
