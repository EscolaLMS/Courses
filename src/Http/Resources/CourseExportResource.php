<?php

namespace EscolaLms\Courses\Http\Resources;

use EscolaLms\Auth\Traits\ResourceExtandable;
use EscolaLms\Courses\Models\Course;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseExportResource extends JsonResource
{
    use ResourceExtandable;

    public static function sanitizePath(string $path): string
    {
        return preg_replace('/courses\/[0-9]+\//', '', $path);
    }

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
            'title' => $course->title,
            'summary' => $course->summary,
            'image_path' => self::sanitizePath($course->image_path),
            'video_path' => self::sanitizePath($course->video_path),
            'base_price' => $course->base_price,
            'duration' => $course->duration,
            // TODO add author export ?
            'author_id' => $course->author_id,
            // TODO add scorm export
            //'scorm' => $this->when($course->scorm_id !== null, fn () => ScormResource::make($course->scorm)),
            'active' => $course->active,
            'subtitle' => $course->subtitle,
            'language' => $course->language,
            'description' => $course->description,
            'level' => $course->level,
            'lessons' => LessonExportResource::collection($course->lessons->sortBy('order')),
            'poster_path' => self::sanitizePath($course->poster_path),
        ];

        return self::apply($fields, $this);
    }
}
