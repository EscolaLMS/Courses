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
            'course_title' => $course->title,
            'duration' => $course->duration,
            'price' => $course->price_brutto,
            'price_netto' => $course->price,
            'strike_out_price' => $course->strike_out_price,
            'active' => $course->is_active,
            'shortDesc' => [
                'title' => $course->title ?? "What you’ll learn",
                'image' => $course->image_path,
            ],
            'categories' => $course->categories,
            'instructor_id' => $course->author_id,
            'created_at' => $course->created_at,
            'updated_at' => $course->updated_at,
            'finished_at' => $finishedAt ?? null,
            'course_image' => $course->image_path
        ];

    }
}
