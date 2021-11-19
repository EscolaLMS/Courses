<?php

namespace EscolaLms\Courses\Http\Resources\Admin;

use EscolaLms\Auth\Traits\ResourceExtandable;
use EscolaLms\Courses\Http\Resources\ScormResource;
use EscolaLms\Courses\Models\Course;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseWithProgramAdminResource extends JsonResource
{
    use ResourceExtandable;

    public function __construct(Course $resource)
    {
        parent::__construct($resource);
    }

    public function getResource(): Course
    {
        return $this->resource;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function toArray($request): array
    {
        $this->withoutWrapping();

        $course = $this->getResource();

        $fields = [
            'id' => $course->getKey(),
            'title' => $course->title,
            'summary' => $course->summary,
            'image_path' => $course->image_path,
            'image_url' => $course->image_url,
            'video_path' => $course->video_path,
            'video_url' => $course->video_url,
            'base_price' => $course->base_price,
            'duration' => $course->duration,
            'author_id' => $course->author_id,
            'scorm_id' => $course->scorm_id,
            'scorm' => $this->when($course->scorm_id !== null, fn () => ScormResource::make($course->scorm)),
            'active' => $course->active,
            'subtitle' => $course->subtitle,
            'language' => $course->language,
            'description' => $course->description,
            'level' => $course->level,
            'lessons' => LessonWithTopicsAdminResource::collection($course->lessons->sortBy('order')),
            'poster_path' => $course->poster_path,
            'poster_url' => $course->poster_url,
        ];

        return self::apply($fields, $this);
    }
}
