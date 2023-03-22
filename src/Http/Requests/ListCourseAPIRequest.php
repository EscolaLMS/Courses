<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Models\Course;
use Illuminate\Foundation\Http\FormRequest;

class ListCourseAPIRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return isset($user) ? $user->can('list', Course::class) : true;
    }

    public function rules(): array
    {
        return [
            'categories' => ['array', 'prohibited_unless:category_id,null'],
            'categories.*' => ['integer'],
            'category_id' => ['integer'],
        ];
    }
}
