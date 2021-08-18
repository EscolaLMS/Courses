<?php

namespace EscolaLms\Courses\Http\Resources;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\ValueObjects\CourseContent;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;
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

        if ($course && $request->user()) {
            $courseProgressCollection = CourseProgressCollection::make($request->user(), $course);
            $finishedAt = $courseProgressCollection->getFinishDate();
        }

        return [
            'id' => $course->getKey(),
            'title' =>  $course->title,
            'summary' =>  $course->summary,
            'image_path' =>  $course->image_path,
            'video_path' =>  $course->video_path,
            'base_price' =>  $course->base_price,
            'duration' =>  $course->duration,
            'author_id' => $course->author_id,
            'scorm_id' => $course->scorm_id,
            'active' =>  $course->active,
            'subtitle' =>  $course->subtitle,
            'language' =>  $course->language,
            'description' =>  $course->description,
            'level' =>  $course->level,
        ];
    }
}
