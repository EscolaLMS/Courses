<?php


namespace EscolaLms\Courses\Services;


use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Events\CourseCompleted;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\H5PUserProgress;
use EscolaLms\Courses\Models\Topic;
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

    public function ping(Authenticatable $user, Topic $topic): void
    {
        CourseProgressCollection::make($user, $topic->lesson->course)->ping($topic);
    }

    public function h5p(Authenticatable $user, Topic $topic, string $event, $json): H5PUserProgress
    {
        return $this->courseH5PProgressContract->store($topic, $user, $event, $json);
    }

    private function getMaxScore(array $data)
    {
        if (isset($data['score']) && is_array($data['score'])) {
            if (isset($data['score']['max'])) {
                return $data['score']['max'];
            }
        }

        if (isset($data['max_score'])) {
            return $data['max_score'];
        }

        return null;
    }

    private function getScore(array $data)
    {
        if (isset($data['score']) && is_array($data['score'])) {
            if (isset($data['score']['raw'])) {
                return $data['score']['raw'];
            }
        }

        if (isset($data['score'])) {
            return $data['score'];
        }

        return null;
    }
}
