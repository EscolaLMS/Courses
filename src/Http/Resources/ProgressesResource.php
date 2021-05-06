<?php


namespace EscolaLms\Courses\Http\Resources;


use EscolaLms\Courses\Models\Course;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProgressesResource extends ResourceCollection
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
        return $this->collection->transform(function (Course $course) {
            return new ProgressResource($course);
        })->toArray();
    }


}