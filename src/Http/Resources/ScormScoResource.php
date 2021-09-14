<?php

namespace EscolaLms\Courses\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Peopleaps\Scorm\Model\ScormScoModel;

class ScormScoResource extends JsonResource
{

    public function __construct(ScormScoModel $resource)
    {
        $this->resource = $resource;
    }

    public function getResource(): ScormScoModel
    {
        return $this->resource;
    }

    public function toArray($request): array
    {
        return [
            'id' => $this->getResource()->getKey(),
            'uuid' => $this->getResource()->uuid,
            'entry_url' => $this->getResource()->entry_url,
            'title' => $this->getResource()->title,
        ];
    }
}
