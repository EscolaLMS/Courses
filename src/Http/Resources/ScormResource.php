<?php

namespace EscolaLms\Courses\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Peopleaps\Scorm\Model\ScormModel;

class ScormResource extends JsonResource
{

    public function __construct(ScormModel $resource)
    {
        $this->resource = $resource;
    }

    public function getResource(): ScormModel
    {
        return $this->resource;
    }

    public function toArray($request): array
    {
        return [
            'id' => $this->getResource()->getKey(),
            'uuid' => $this->getResource()->uuid,
            'version' => $this->getResource()->version,
            // @phpstan-ignore-next-line
            'scos' => ScormScoResource::collection($this->getResource()->scos),
        ];
    }
}
