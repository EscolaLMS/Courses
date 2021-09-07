<?php

namespace EscolaLms\Courses\Policies;

use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Models\Lesson;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class LessonPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Lesson $lesson)
    {
        return $user->can('update', $lesson->course);
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $user->can('update', $lesson->course);
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $user->can('update', $lesson->course);
    }

    public function attend(?User $user, Lesson $lesson)
    {
        return Gate::check('attend', $lesson->course);
    }
}
