<?php

namespace EscolaLms\Courses\Policies;

use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Models\Topic;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class TopicPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Topic $topic): bool
    {
        return $user->can('update', $topic->lesson); // this calls `update` method from LessonPolicy
    }

    public function update(User $user, Topic $topic): bool
    {
        return $user->can('update', $topic->lesson); // this calls `update` method from LessonPolicy
    }

    public function delete(User $user, Topic $topic): bool
    {
        return $user->can('update', $topic->lesson); // this calls `update` method from LessonPolicy
    }

    public function attend(?User $user, Topic $topic): bool
    {
        return Gate::check('attend', $topic->lesson); // this calls `attend` method from LessonPolicy
    }

    public function clone(User $user, Topic $topic): bool
    {
        return $user->can('update', $topic->lesson); // this calls `update` method from LessonPolicy
    }
}
