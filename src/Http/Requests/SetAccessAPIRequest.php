<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Http\Requests\Abstracts\CourseAccessAPIRequest;
use Illuminate\Validation\Rule;

class SetAccessAPIRequest extends CourseAccessAPIRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'groups' => ['sometimes', 'array'],
            'groups.*' => ['sometimes', 'integer', Rule::exists('groups', 'id')],
            'users' => ['sometimes', 'array'],
            'users.*' => ['sometimes', 'integer', Rule::exists('users', 'id')],
        ]);
    }
}
