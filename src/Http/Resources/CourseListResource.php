<?php

namespace EscolaLms\Courses\Http\Resources;

use EscolaLms\Auth\Traits\ResourceExtandable;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseListResource extends JsonResource
{
    use ResourceExtandable;

    public function toArray($request)
    {
        $fields = [
            'id' => $this->resource->id,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'title' => $this->resource->title,
            'summary' => $this->resource->summary,
            'image_path' => $this->resource->image_path,
            'video_path' => $this->resource->video_path,
            'duration' => $this->resource->duration,
            'author_id' => $this->resource->author_id,
            'author' => $this->resource->author ? TutorResource::make($this->resource->author) : null,
            'authors' => $this->resource->authors ? TutorResource::collection($this->resource->authors) : [],
            'status' => $this->resource->status,
            'subtitle' => $this->resource->subtitle,
            'language' => $this->resource->language,
            'description' => $this->resource->description,
            'categories' => $this->resource->categories,
            'tags' => $this->resource->tags,
            'level' => $this->resource->level,
            'poster_path' => $this->resource->poster_path,
            'active_from' => $this->resource->active_from,
            'active_to' => $this->resource->active_to,
            'hours_to_complete' => $this->resource->hours_to_complete,
            'findable' => $this->resource->findable,
            'scorm_sco_id' => $this->resource->scorm_sco_id,
            'target_group' => $this->resource->target_group,
            'users_count' => $this->resource->users_count,
            'image_url' => $this->resource->image_url,
            'video_url' => $this->resource->video_url,
            'poster_url' => $this->resource->poster_url,
            'teaser_url' => $this->resource->teaser_url,
            'public' => $this->resource->public ?? false,
            'fields' => $this->resource->fields,
        ];

        return self::apply($fields, $this);
    }
}
