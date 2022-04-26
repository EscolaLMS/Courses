<?php

namespace EscolaLms\Courses\Http\Resources;

use EscolaLms\Auth\Traits\ResourceExtandable;
use EscolaLms\Courses\Facades\Topic;
use Illuminate\Http\Resources\Json\JsonResource;

class TopicSimpleResource extends TopicResource
{
    public function toArray($request)
    {
        $response = parent::toArray($request);
        if (!$this->preview) {
            unset($response['topicable']);
            unset($response['resources']);
        }

        return $response;
    }
}
