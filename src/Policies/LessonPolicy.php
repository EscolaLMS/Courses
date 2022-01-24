<?php

namespace EscolaLms\Courses\Policies;

use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Models\Lesson;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class LessonPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Lesson $lesson): bool
    {
        return $user->can('update', $lesson->course); // this calls `update` method from CoursePolicy
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $user->can('update', $lesson->course); // this calls `update` method from CoursePolicy
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $user->can('update', $lesson->course); // this calls `update` method from CoursePolicy
    }

    public function attend(?User $user, Lesson $lesson): bool
    {
        return Gate::check('attend', $lesson->course); // this calls `attend` method from CoursePolicy
    }

    public function clone(User $user, Lesson $lesson): bool
    {
        return $user->can('update', $lesson->course); // this calls `update` method from CoursePolicy
    }
}
