<?php

namespace EscolaLms\Courses\Services;

use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Events\CourseCompleted;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Group;
use EscolaLms\Courses\Models\H5PUserProgress;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\User as CoursesUser;
use EscolaLms\Courses\Repositories\Contracts\CourseH5PProgressRepositoryContract;
use EscolaLms\Courses\Services\Contracts\ProgressServiceContract;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

class ProgressService implements ProgressServiceContract
{
    private CourseH5PProgressRepositoryContract $courseH5PProgressContract;

    public function __construct(
        CourseH5PProgressRepositoryContract $courseH5PProgressContract
    ) {
        $this->courseH5PProgressContract = $courseH5PProgressContract;
    }

    public function getByUser(User $user): Collection
    {
        $progresses = new Collection();
        if (!$user instanceof CoursesUser) {
            $user = CoursesUser::find($user->getKey());
        }
        /** @var CoursesUser $user */
        foreach ($user->courses as $course) {
            $progresses->push(CourseProgressCollection::make($user, $course));
        }
        foreach ($user->groups as $group) {
            /** @var Group $group */
            if (!$group instanceof Group) {
                $group = Group::find($group->getKey());
            }
            foreach ($group->courses as $course) {
                if (!$progresses->contains('id', $course->getKey())) {
                    $progresses->push(CourseProgressCollection::make($user, $course));
                }
            }
        }
        return $progresses;
    }

    public function update(Course $course, User $user, array $progress): CourseProgressCollection
    {
        $courseProgressCollection = CourseProgressCollection::make($user, $course);
        $result = $courseProgressCollection->setProgress($progress);

        if (!$user instanceof CoursesUser) {
            $user = CoursesUser::find($user->getKey());
        }

        assert($user instanceof CoursesUser);

        $courseIsFinished = $courseProgressCollection->isFinished();
        $userHasCourseMarkedAsFinished = $user->finishedCourse($course->getKey());

        if ($courseIsFinished && !$userHasCourseMarkedAsFinished) {
            $user->courses()->updateExistingPivot($course->getKey(), ['finished' => true]);
            event(new CourseCompleted($courseProgressCollection->getCourse(), $user));
        } elseif (!$courseIsFinished && $userHasCourseMarkedAsFinished) {
            $user->courses()->updateExistingPivot($course->getKey(), ['finished' => false]);
        }

        return $result;
    }

    public function ping(User $user, Topic $topic): void
    {
        $course = $topic->lesson->course;

        $courseProgressCollection = CourseProgressCollection::make($user, $course);
        $courseProgressCollection->ping($topic);

        if (!$courseProgressCollection->isFinished() && $user->finishedCourse($course->getKey())) {
            $user->courses()->updateExistingPivot($course->getKey(), ['finished' => false]);
        }
    }

    public function h5p(User $user, Topic $topic, string $event, $json): H5PUserProgress
    {
        return $this->courseH5PProgressContract->store($topic, $user, $event, $json);
    }
}
