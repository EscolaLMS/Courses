<?php

namespace EscolaLms\Courses\Policies;

use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Enum\CoursesPermissionsEnum;
use EscolaLms\Courses\Models\Lesson;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class LessonPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Lesson $lesson): bool
    {
        return $user->can(CoursesPermissionsEnum::LESSON_UPDATE, $lesson->course);
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $user->can(CoursesPermissionsEnum::LESSON_UPDATE, $lesson->course);
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $user->can(CoursesPermissionsEnum::LESSON_UPDATE, $lesson->course);
    }

    public function attend(?User $user, Lesson $lesson): bool
    {
        return Gate::check(CoursesPermissionsEnum::LESSON_ATTEND, $lesson->course);
    }
}
