<?php

namespace EscolaLms\Courses\Policies;

use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Enum\CoursesPermissionsEnum;
use EscolaLms\Courses\Models\Course;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursesPolicy
{
    use HandlesAuthorization;

    public function list(User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $user->can(CoursesPermissionsEnum::COURSE_LIST)
            || $user->can(CoursesPermissionsEnum::COURSE_LIST_OWNED);
    }

    /**
     * @param User $user
     * @param Course $course
     * @return bool
     */
    public function update(User $user, Course $course): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        if ($user->can(CoursesPermissionsEnum::COURSE_UPDATE)) {
            return true;
        }
        if ($user->can(CoursesPermissionsEnum::COURSE_UPDATE_OWNED)) {
            return $course->hasAuthor($user);
        }

        return false;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $user->can(CoursesPermissionsEnum::COURSE_CREATE);
    }

    /**
     * @param User $user
     * @param Course $course
     * @return bool
     */
    public function delete(User $user, Course $course): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        if ($user->can(CoursesPermissionsEnum::COURSE_DELETE)) {
            return true;
        }
        if ($user->can(CoursesPermissionsEnum::COURSE_DELETE_OWNED)) {
            return $course->hasAuthor($user);
        }

        return false;
    }

    /**
     * Does user has access to this course, example user has brought the course
     *
     * @param User|null $user
     * @param Course $course
     * @return bool
     */
    public function attend(?User $user, Course $course): bool
    {
        if ($course->public && $course->is_published) {
            return true;
        }
        if (empty($user)) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->can(CoursesPermissionsEnum::COURSE_ATTEND)) {
            return true;
        }

        if ($user->can(CoursesPermissionsEnum::COURSE_ATTEND_OWNED)) {
            return $course->hasAuthor($user);
        }
        return $course->is_published && $course->hasUser($user);
    }

    public function view(?User $user, Course $course): bool
    {
        if ($course->is_published && $course->findable) {
            return true;
        }

        return $this->attend($user, $course);
    }
}
