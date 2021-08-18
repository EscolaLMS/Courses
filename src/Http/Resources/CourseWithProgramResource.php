<?php

namespace EscolaLms\Courses\Http\Resources;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseWithProgramResource extends JsonResource
{
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
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        $this->withoutWrapping();

        $course = $this->getResource();

        $lessons = $course->lessons->filter(fn (Lesson $lesson) => $lesson->active);

        return [
            'id' => $course->getKey(),
            'title' => $course->title,
            'summary' => $course->summary,
            'image_path' => $course->image_path,
            'image_url' => $course->image_url,
            'video_path' => $course->video_path,
            'video_url' => $course->video_url,
            'base_price' =>  $course->base_price,
            'duration' =>  $course->duration,
            'author_id' => $course->author_id,
            'scorm_id' => $course->scorm_id,
            'active' =>  $course->active,
            'subtitle' =>  $course->subtitle,
            'language' =>  $course->language,
            'description' =>  $course->description,
            'level' =>  $course->level,
            'finishedAt' => $this->when($request->user(), fn () => CourseProgressCollection::make($request->user(), $course)->getFinishDate(), null),
            'lessons' => LessonWithTopicsResource::collection($lessons),
        ];
    }
}
