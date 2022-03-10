<?php

namespace EscolaLms\Courses\Http\Resources;

use EscolaLms\ModelFields\Enum\MetaFieldVisibilityEnum;
use EscolaLms\ModelFields\Facades\ModelFields;
use Illuminate\Http\Resources\Json\JsonResource;

class TutorResource extends JsonResource
{
    public function toArray($request)
    {
        $fields = [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'path_avatar' => $this->path_avatar,
            'interests' => TutorInterestResource::collection($this->interests),
        ];

        return array_merge(
            $fields,
            ModelFields::getExtraAttributesValues($this->resource, MetaFieldVisibilityEnum::PUBLIC)
        );
    }
}
