<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Enum\CoursesPermissionsEnum;
use EscolaLms\Courses\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class CourseAssignableUserListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows(CoursesPermissionsEnum::COURSE_CREATE, Course::class);
    }

    public function rules(): array
    {
        return [
            'search' => ['string'],
        ];
    }
}
