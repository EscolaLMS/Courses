<?php

namespace EscolaLms\Courses\Http\Resources;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\ValueObjects\CourseContent;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->withoutWrapping();

        if ($this->resource instanceof Course) {
            $this->resource = CourseContent::make($this->resource);
        }

        $course = $this->resource->getCourse();

        return [
            'id' => $course->getKey(),
            'title' =>  $course->title,
            'summary' =>  $course->summary,
            'image_path' =>  $course->image_path,
            'video_path' =>  $course->video_path,
            'base_price' =>  $course->base_price,
            'duration' =>  $course->duration,
            'author_id' => $course->author_id,
            'authors' => $course->authors ? TutorResource::collection($course->authors) : [],
            'scorm_sco_id' => $course->scorm_sco_id,
            'status' =>  $course->status,
            'subtitle' =>  $course->subtitle,
            'language' =>  $course->language,
            'description' =>  $course->description,
            'level' =>  $course->level,
            'poster_path' =>  $course->poster_path,
            'active_from' => $course->active_from,
            'active_to' => $course->active_to,
            'hours_to_complete' => $course->hours_to_complete,
            'purchasable' => $course->purchasable,
            'findable' => $course->findable,
            'target_group' => $course->target_group,
            'teaser_url' => $course->teaser_url,
        ];
    }
}
