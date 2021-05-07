<?php


namespace EscolaLms\Courses\Services;


use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Services\Contracts\ProgressServiceContract;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

class ProgressService implements ProgressServiceContract
{

    public function getByUser(User $user): Collection
    {
        $progresses = new Collection();
        foreach ($user->courses as $course) {
            $course->progress = CourseProgressCollection::make($user, $course);
            $progresses->push($course);
        }
        return $progresses;
    }

    public function update(Course $course, Authenticatable $user, array $progress): CourseProgressCollection
    {
        $courseProgressCollection = CourseProgressCollection::make($user, $course);
        $result = $courseProgressCollection->setProgress($progress);

        if (
            $courseProgressCollection->isFinished() &&
            $user instanceof User &&
            !$user->courses()->where('course_id', $course->getKey())->wherePivot('finished', true)->exists()
        ) {
            $user->courses()->updateExistingPivot($course->getKey(), ['finished' => true]);
            event(new CourseCompleted($courseProgressCollection->getCourse(), $user));
        }

        return $result;
    }



}