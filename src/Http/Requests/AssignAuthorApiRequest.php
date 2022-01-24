<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\User;
use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use Illuminate\Foundation\Http\FormRequest;

class AssignAuthorApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return isset($user) ? $user->can('update', $this->getCourse()) : false;
    }

    public function rules(): array
    {
        return [];
    }

    public function getCourse(): ?Course
    {
        return Course::find($this->route('course'));
    }

    public function getTutor(): ?User
    {
        return User::find($this->route('id'));
    }
}
