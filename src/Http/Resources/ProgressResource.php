<?php


namespace EscolaLms\Courses\Http\Resources;


use EscolaLms\Courses\ValueObjects\CourseContent;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $this->withoutWrapping();
        return [
            'course' => new CourseResource(CourseContent::make($this->resource)),
            'progress' => $this->progress->toArray(),
            'finish_date' => $this->progress->getFinishDate()
        ];
    }
}
