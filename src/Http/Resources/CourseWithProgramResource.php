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

        $lessons = $course->lessons->filter(fn (Lesson $lesson) => $lesson->active)->sortBy('order');

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
            'authors' => $course->authors ? TutorResource::collection($course->authors) : [],
            'scorm_sco_id' => $course->scorm_sco_id,
            'scorm_sco' => $this->when($course->scorm_sco_id !== null, fn () => ScormScoResource::make($course->scormSco)),
            'status' =>  $course->status,
            'subtitle' =>  $course->subtitle,
            'language' =>  $course->language,
            'description' => $course->description,
            'level' =>  $course->level,
            'lessons' => LessonWithTopicsResource::collection($lessons),
            'poster_path' =>  $course->poster_path,
            'poster_url' =>  $course->poster_url,
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
