<?php

namespace EscolaLms\Courses\Http\Resources;

use EscolaLms\Auth\Traits\ResourceExtandable;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseSimpleResource extends JsonResource
{
    use ResourceExtandable;

    public function toArray($request)
    {
        $fields = [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'title' => $this->title,
            'summary' => $this->summary,
            'image_path' => $this->image_path,
            'video_path' => $this->video_path,
            'base_price' => $this->base_price,
            'duration' => $this->duration,
            'author_id' => $this->author_id,
            'author' => $this->author,
            'authors' => $this->authors,
            'active' => $this->active,
            'subtitle' => $this->subtitle,
            'language' => $this->language,
            'description' => $this->description,
            'categories' => $this->categories,
            'tags' => $this->tags,
            'level' => $this->level,
            'lessons' => LessonSimpleResource::collection($this->lessons),
            'poster_path' => $this->poster_path,
            'active_from' => $this->active_from,
            'active_to' => $this->active_to,
            'hours_to_complete' => $this->hours_to_complete,
            'purchasable' => $this->purchasable,
            'findable' => $this->findable,
            'scorm_sco_id' => $this->scorm_sco_id,
            'target_group' => $this->target_group,
            'users_count' => $this->users_count,
            'image_url' => $this->image_url,
            'video_url' => $this->video_url,
            'poster_url' => $this->poster_url,
        ];
        return self::apply($fields, $this);
    }
}
