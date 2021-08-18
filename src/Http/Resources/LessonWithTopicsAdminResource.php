<?php

namespace EscolaLms\Courses\Http\Resources;

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
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        $lesson = $this->getResource();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'image_path' => $this->image_path,
            'video_path' => $this->video_path,
            'base_price' => $this->base_price,
            'duration' => $this->duration,
            'active' => $this->active,
            'author_id' => $this->author_id,
            'topics' => TopicResource::collection($lesson->topics)
        ];
    }
}
