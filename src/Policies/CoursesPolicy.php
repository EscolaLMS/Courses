<?php

namespace EscolaLms\Courses\Policies;

use EscolaLms\Courses\Enum\CoursesPermissionsEnum;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Core\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CoursesPolicy
{
    use HandlesAuthorization;

    public function list(User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $user->can(CoursesPermissionsEnum::COURSE_LIST);
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
        if ($user->can(CoursesPermissionsEnum::COURSE_UPDATE_OWNED) && $course->author_id === $user->id) {
            return true;
        }
        if ($user->can(CoursesPermissionsEnum::COURSE_UPDATE_OWNED) && $course->author_id !== $user->id) {
            return Response::deny('You do not own this course.');
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
        if ($user->can(CoursesPermissionsEnum::COURSE_DELETE_OWNED) && $course->author_id === $user->id) {
            return true;
        }
        if ($user->can(CoursesPermissionsEnum::COURSE_DELETE_OWNED) && $course->author_id !== $user->id) {
            return Response::deny('You do not own this course.');
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
        if (intval($course->base_price) === 0 && $this->buy($user, $course)) {
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
        if ($user->can(CoursesPermissionsEnum::COURSE_ATTEND_OWNED) && $course->author_id === $user->id) {
            return true;
        }

        return $course->is_active && ($course->users()->where('users.id', $user->getKey())->exists() || $course->groups()->whereHas('users', fn ($query) => $query->where('users.id', $user->getKey()))->exists());
    }

    public function view(?User $user, Course $course): bool
    {
        if ($course->is_active && $course->findable) {
            return true;
        }

        return $this->attend($user, $course);
    }

    public function buy(?User $user, Course $course): bool
    {
        return $course->is_active && $course->purchasable;
    }
}
