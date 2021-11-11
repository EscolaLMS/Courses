<?php

namespace EscolaLms\Courses\Http\Resources;

use EscolaLms\Courses\Models\TopicResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TopicExportResourceResource extends JsonResource
{
    public function __construct(TopicResource $resource)
    {
        $this->resource = $resource;
    }

    public function getResource(): TopicResource
    {
        return $this->resource;
    }

    public function toArray($request): array
    {
        return [
            'path' => CourseExportResource::sanitizePath($this->getResource()->path),
            'name' => $this->getResource()->name,
        ];
    }
}
