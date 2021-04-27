<?php

namespace EscolaLms\Courses\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
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
            'summary' => $this->summary,
            'image_path' => $this->image_path,
            'video_path' => $this->video_path,
            'base_price' => $this->base_price,
            'duration' => $this->duration,
            'active' => $this->active,
            'author_id' => $this->author_id
        ];
    }
}
