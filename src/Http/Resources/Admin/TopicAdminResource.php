<?php

namespace EscolaLms\Courses\Http\Resources\Admin;

use EscolaLms\Courses\Facades\Topic;
use EscolaLms\Courses\Http\Resources\TopicResourceResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TopicAdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $topicable = $this->topicable;

        if (Topic::getResourceClass($this->topicable_type, 'admin')) {
            $resourceClass = Topic::getResourceClass($this->topicable_type, 'admin');
            $resource = new $resourceClass($this->topicable);
            $topicable = $resource->toArray($request);
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'lesson_id' => $this->lesson_id,
            'active' => $this->active,
            'preview' => $this->preview,
            'topicable_id' => $this->topicable_id,
            'topicable_type' => $this->topicable_type,
            'topicable' => $topicable,
            'summary' => $this->summary,
            'introduction' => $this->introduction,
            'description' => $this->description,
            'resources' => TopicResourceResource::collection($this->resources),
            'order' => $this->order,
            'json' => $this->json,
            'can_skip' => $this->can_skip,
        ];
    }
}
