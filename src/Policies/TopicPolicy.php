<?php

namespace EscolaLms\Courses\Policies;

use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Enum\CoursesPermissionsEnum;
use EscolaLms\Courses\Models\Topic;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class TopicPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Topic $topic): bool
    {
        return $user->can(CoursesPermissionsEnum::TOPIC_UPDATE, $topic->lesson);
    }

    public function update(User $user, Topic $topic): bool
    {
        return $user->can(CoursesPermissionsEnum::TOPIC_UPDATE, $topic->lesson);
    }

    public function delete(User $user, Topic $topic): bool
    {
        return $user->can(CoursesPermissionsEnum::TOPIC_UPDATE, $topic->lesson);
    }

    public function attend(?User $user, Topic $topic): bool
    {
        return Gate::check(CoursesPermissionsEnum::TOPIC_ATTEND, $topic->lesson);
    }
}
