<?php

namespace EscolaLms\Courses\Http\Resources;

use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
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
            'id' => $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'duration' => $this->duration,
            'active' => $this->active,
            'topics' => TopicResource::collection($topics),
            'order' => $this->order,
            'active_from' => $this->active_from,
            'active_to' => $this->active_to,
            'lessons' => LessonWithTopicsResource::collection($lessons),
        ];
    }
}
