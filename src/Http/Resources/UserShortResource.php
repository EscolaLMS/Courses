<?php

namespace EscolaLms\Courses\Http\Resources;

use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Models\User as CoursesUser;
use Illuminate\Http\Resources\Json\JsonResource;

class UserShortResource extends JsonResource
{
    public function __construct(User $user)
    {
        if ($user instanceof CoursesUser) {
            $user = CoursesUser::find($user->getKey());
        }
        $this->resource = $user;
    }

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
