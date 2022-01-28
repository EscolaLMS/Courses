<?php

namespace EscolaLms\Courses\Http\Resources;

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
        ];

        $this->settings->each(function ($setting) use (&$fields) {
            if (str_starts_with($setting->key, 'additional_field:')) {
                $fields[str_replace('additional_field:', '', $setting->key)] = $setting->value;
            }
        });

        return $fields;
    }
}
