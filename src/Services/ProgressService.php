<?php

namespace EscolaLms\Courses\Services;

use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Enum\CourseStatusEnum;
use EscolaLms\Courses\Events\CourseAccessFinished;
use EscolaLms\Courses\Events\CourseAccessStarted;
use EscolaLms\Courses\Events\CourseFinished;
use EscolaLms\Courses\Events\CourseStarted;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\CourseProgress;
use EscolaLms\Courses\Models\Group;
use EscolaLms\Courses\Models\H5PUserProgress;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\User as CoursesUser;
use EscolaLms\Courses\Repositories\Contracts\CourseH5PProgressRepositoryContract;
use EscolaLms\Courses\Services\Contracts\ProgressServiceContract;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProgressService implements ProgressServiceContract
{
    private CourseH5PProgressRepositoryContract $courseH5PProgressContract;

    public function __construct(
        CourseH5PProgressRepositoryContract $courseH5PProgressContract
    )
    {
        $this->courseH5PProgressContract = $courseH5PProgressContract;
    }

    public function getByUser(User $user): Collection
    {
        $progresses = new Collection();
        if (!$user instanceof CoursesUser) {
            $user = CoursesUser::find($user->getKey());
        }
        /** @var CoursesUser $user */
        foreach ($user->courses->where('status', '=', CourseStatusEnum::PUBLISHED) as $course) {
            $progresses->push(CourseProgressCollection::make($user, $course));
        }
        foreach ($user->groups as $group) {
            if (!$group instanceof Group) {
                $group = Group::find($group->getKey());
            }
            /** @var Group $group */
            foreach ($group->courses->where('status', '=', CourseStatusEnum::PUBLISHED) as $course) {
                if (!$progresses->contains(fn(CourseProgressCollection $collection) => $collection->getCourse()->getKey() === $course->getKey())) {
                    $progresses->push(CourseProgressCollection::make($user, $course));
                }
            }
        }

        return $progresses
            ->sortByDesc(fn(CourseProgressCollection $collection) => $collection->getCourse()->pivot->created_at)
            ->values();
    }

    public function getByUserPaginated(User $user, ?OrderDto $orderDto = null, ?int $perPage = 20): LengthAwarePaginator
    {
        $userId = $user->getKey();
        $progresses = new Collection();

        $query = Course::query()
            ->select('courses.*')
            ->leftJoinSub('SELECT course_id, MAX(created_at) as user_pivot_created_at FROM course_user GROUP BY course_id', 'course_user', function (JoinClause $join) {
                $join->on('courses.id', '=', 'course_user.course_id');
            })
            ->leftJoinSub('SELECT course_id, MAX(created_at) as group_pivot_created_at FROM course_group GROUP BY course_id', 'course_group', function (JoinClause $join) {
                $join->on('courses.id', '=', 'course_group.course_id');
            })
            ->whereHas('users', function (Builder $query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->orWhereHas('groups', function (Builder $query) use ($userId) {
                $query->whereHas('users', function (Builder $query) use ($userId) {
                    $query->where('users.id', $userId);
                });
            })
            ->groupBy('courses.id', 'course_user.user_pivot_created_at', 'course_group.group_pivot_created_at');

        if (request()->has('status')) {
            $query = match (request()->get('status')) {
                'planned' => $this->applyPlannedFilter($query, $user->getKey())
            };
        }

        dd($query->get()->toArray());

        $order = $orderDto->getOrder() ?? 'desc';

        if ($orderDto->getOrderBy() && $orderDto->getOrderBy() !== 'obtained') {
            $query->orderBy($orderDto->getOrderBy(), $order);
        } else {
            if (DB::connection()->getDriverName() === 'pgsql') {
                $order = $order === 'desc' ? $order . ' NULLS LAST' : $order . ' NULLS FIRST';
            }
            $query->orderByRaw("LEAST(COALESCE(user_pivot_created_at, group_pivot_created_at), COALESCE(group_pivot_created_at, user_pivot_created_at)) $order");
        }

        $courses = $query->paginate($perPage);

        foreach ($courses as $course) {
            $progresses->push(CourseProgressCollection::make($user, $course));
        }

        return new LengthAwarePaginator(
            $progresses->values(),
            $courses->total(),
            $courses->perPage(),
            $courses->currentPage(),
            ['path' => $courses->path()]
        );
    }

    public function update(Course $course, User $user, array $progress): CourseProgressCollection
    {
        $courseProgressCollection = CourseProgressCollection::make($user, $course);

        if ($courseProgressCollection->courseCanBeProgressed()) {
            if ($courseProgressCollection->getProgress()->count() === 0) {
                event(new CourseAccessStarted($user, $course));
                event(new CourseStarted($user, $course));
            }
            if (!empty($progress)) {
                $courseProgressCollection->setProgress($progress);
            }

            if (!$user instanceof CoursesUser) {
                $user = CoursesUser::find($user->getKey());
            }

            assert($user instanceof CoursesUser);

            $courseIsFinished = $courseProgressCollection->isFinished();
            $userHasCourseMarkedAsFinished = $user->finishedCourse($course->getKey());

            if ($courseIsFinished && !$userHasCourseMarkedAsFinished) {
                $user->courses()->updateExistingPivot($course->getKey(), ['finished' => true]);
                event(new CourseAccessFinished($user, $courseProgressCollection->getCourse()));
                event(new CourseFinished($user, $courseProgressCollection->getCourse()));
            } elseif (!$courseIsFinished && $userHasCourseMarkedAsFinished) {
                $user->courses()->updateExistingPivot($course->getKey(), ['finished' => false]);
            }
        }

        return $courseProgressCollection;
    }

    public function ping(User $user, Topic $topic): CourseProgressCollection
    {
        $course = $topic->course;

        $courseProgressCollection = CourseProgressCollection::make($user, $course);

        if ($courseProgressCollection->topicCanBeProgressed($topic)) {
            $courseProgressCollection->ping($topic);

            if (!$courseProgressCollection->isFinished() && $user->finishedCourse($course->getKey())) {
                $user->courses()->updateExistingPivot($course->getKey(), ['finished' => false]);
            }
        }

        return $courseProgressCollection;
    }

    public function h5p(User $user, Topic $topic, string $event, $json): ?H5PUserProgress
    {
        $courseProgressCollection = CourseProgressCollection::make($user, $topic->course);

        if ($courseProgressCollection->topicCanBeProgressed($topic)) {
            return $this->courseH5PProgressContract->store($topic, $user, $event, $json);
        }
        return null;
    }

    private function applyPlannedFilter($query, $userId)
    {
        $totalSpentTimeSubquery = CourseProgress::query()
            ->select('topics.id as topic_id')
            ->addSelect(DB::raw('SUM(seconds) as total_spent_time'))
            ->join('topics', 'course_progress.topic_id', '=', 'topics.id')
            ->where('user_id', $userId)
            ->groupBy('topics.id');

        return $query
            ->leftJoin('lessons', 'courses.id', '=', 'lessons.course_id')
            ->leftJoin('topics', 'lessons.id', '=', 'topics.lesson_id')
            ->leftJoinSub($totalSpentTimeSubquery, 'total_spent_time', function (JoinClause $join) {
                $join->on('topics.id', '=', 'total_spent_time.topic_id');
            })
            ->havingRaw('total_spent_time.total_spent_time = 0')
            ->groupBy('courses.id', 'total_spent_time.total_spent_time');
    }
}
