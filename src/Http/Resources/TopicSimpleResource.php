<?php

namespace EscolaLms\Courses\Http\Resources;

use EscolaLms\Courses\Facades\Topic;
use Illuminate\Http\Resources\Json\JsonResource;

class TopicSimpleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'lesson_id' => $this->resource->lesson_id,
            'active' => $this->resource->active,
            'preview' => $this->resource->preview,
            'topicable_id' => $this->resource->topicable_id,
            'topicable_type' => $this->resource->topicable_type,
            'summary' => $this->resource->summary,
            'introduction' => $this->resource->introduction,
            'description' => $this->resource->description,
            'order' => $this->resource->order,
            'json' => $this->resource->json,
            'can_skip' => $this->resource->can_skip,
            'duration' => $this->resource->duration,
        ];
    }
}
