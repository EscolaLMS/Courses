<?php

namespace EscolaLms\Courses\Http\Requests;

use App\Models\Course;
use EscolaLms\Courses\Enum\CoursesPermissionsEnum;
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
