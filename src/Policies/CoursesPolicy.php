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
    public function attend(User $user, Course $course)
    {
        if (intval($course->base_price) === 0) {
            return true;
        }
        if ($user->hasRole('admin')) {
            return true;
        }
        // TODO: replace this with actual logic
        return $user->can('attend course');
    }
}
