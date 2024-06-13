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
            // @phpstan-ignore-next-line
            'uuid' => $this->getResource()->uuid,
            // @phpstan-ignore-next-line
            'entry_url' => $this->getResource()->entry_url,
            // @phpstan-ignore-next-line
            'title' => $this->getResource()->title,
        ];
    }
}
