<?php


namespace EscolaLms\Courses\Http\Resources;

use EscolaLms\Courses\Models\TopicResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TopicResourceResource extends JsonResource
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
            'id' => $this->getResource()->id,
            'path' => $this->getResource()->path,
            'name' => $this->getResource()->name,
            'url' => $this->getResource()->url,
            'topic_id' => $this->getResource()->topic_id,
        ];
    }
}
