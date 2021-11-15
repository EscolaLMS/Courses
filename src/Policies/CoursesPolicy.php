<?php

namespace EscolaLms\Courses\Policies;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Core\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CoursesPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Course $course
     * @return bool
     */
    public function update(User $user, Course $course)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        if ($user->can('update course') && $course->author_id === $user->id) {
            return true;
        };
        if ($user->can('update course') && $course->author_id !== $user->id) {
            return Response::deny('You do not own this course.');
        };

        return false;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $user->can('create course');
    }

    /**
     * @param User $user
     * @param Course $course
     * @return bool
     */
    public function delete(User $user, Course $course)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        if ($user->can('update course') && $course->author_id === $user->id) {
            return true;
        };
        if ($user->can('update course') && $course->author_id !== $user->id) {
            return Response::deny('You do not own this course.');
        };

        return false;
    }

    /**
     * Does user has access to this course, example user has brought the course
     *
     * @param User $user
     * @param Course $course
     * @return bool
     */
    public function attend(?User $user, Course $course)
    {
        if (intval($course->base_price) === 0 && $this->buy($user, $course)) {
            return true;
        }

        if (empty($user)) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->can('update course') && $course->author_id === $user->id) {
            return true;
        };

        return $course->is_active && ($course->users()->where('users.id', $user->getKey())->exists() || $course->groups()->whereHas('users', fn ($query) => $query->where('users.id', $user->getKey()))->exists());
    }

    public function view(?User $user, Course $course)
    {
        if ($course->is_active && $course->findable) {
            return true;
        }

        return $this->attend($user, $course);
    }

    public function buy(?User $user, Course $course)
    {
        return $course->is_active && $course->purchasable;
    }
}
