<?php

namespace EscolaLms\Courses\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TopicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'lesson_id' => $this->lesson_id,
            'active' => $this->active,
            'preview' => $this->preview,
            'topicable_id' => $this->topicable_id,
            'topicable_type' => $this->topicable_type,
            'summary' => $this->summary,
        ];
    }
}
