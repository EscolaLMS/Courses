<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ListAuthoredCourseAPIRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('list', Course::class);
    }

    public function rules(): array
    {
        return [
            'order_by' => ['string', 'in:id,title,created_at,status'],
            'order' => ['string', 'in:asc,desc'],
        ];
    }
}
