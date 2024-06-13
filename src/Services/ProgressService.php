<?php

namespace EscolaLms\Courses\Services;

use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Enum\CourseStatusEnum;
use EscolaLms\Courses\Enum\ProgressFilterEnum;
use EscolaLms\Courses\Enum\ProgressStatus;
use EscolaLms\Courses\Events\CourseAccessFinished;
use EscolaLms\Courses\Events\CourseAccessStarted;
use EscolaLms\Courses\Events\CourseFinished;
use EscolaLms\Courses\Events\CourseStarted;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Group;
use EscolaLms\Courses\Models\H5PUserProgress;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\User as CoursesUser;
use EscolaLms\Courses\Repositories\Contracts\CourseH5PProgressRepositoryContract;
use EscolaLms\Courses\Services\Contracts\ProgressServiceContract;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
            /** @var CoursesUser $user */
            $user = CoursesUser::find($user->getKey());
        }

        $courses = $user->courses()->where('status', '=', CourseStatusEnum::PUBLISHED)->get();
        /** @var Course $course */
        foreach ($courses as $course) {
            $progresses->push(CourseProgressCollection::make($user, $course));
        }

        $groups = $user->groups->merge($this->getParentGroups($user->groups));
        foreach ($groups as $group) {
            if (!$group instanceof Group) {
                $group = Group::find($group->getKey());
            }

            $courses = $group->courses()->where('status', '=', CourseStatusEnum::PUBLISHED)->get();
            foreach ($courses as $course) {
                if (!$progresses->contains(fn(CourseProgressCollection $collection) => $collection->getCourse()->getKey() === $course->getKey())) {
                    $progresses->push(CourseProgressCollection::make($user, $course));
                }
            }
        }

        return $progresses
            ->sortByDesc(fn(CourseProgressCollection $collection) => $collection->getCourse()->pivot->created_at)
            ->values();
    }

    public function getByUserPaginated(User $user, ?OrderDto $orderDto = null, ?int $perPage = 20, ?string $filter = null): LengthAwarePaginator
    {
        $userId = $user->getKey();
        $progresses = new Collection();

        $query = $this->getBaseQuery($userId);
        $query = $this->applyFilters($query, $userId, $filter);
        $query = $this->orderQuery($query, $orderDto);

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

            if (!$user instanceof CoursesUser) {
                /** @var CoursesUser $user */
                $user = CoursesUser::find($user->getKey());
            }
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

    private function getBaseQuery(int $userId): Builder
    {
        return Course::dontCache()
            ->leftJoinSub('SELECT course_id, MAX(created_at) as user_pivot_created_at FROM course_user GROUP BY course_id', 'course_user', function ($join) {
                $join->on('courses.id', '=', 'course_user.course_id');
            })
            ->leftJoinSub('SELECT course_id, MAX(created_at) as group_pivot_created_at FROM course_group GROUP BY course_id', 'course_group', function ($join) {
                $join->on('courses.id', '=', 'course_group.course_id');
            })
            ->where(function (Builder $query) use ($userId) {
                $query
                    ->whereHas('users', function (Builder $query) use ($userId) {
                        $query->where('users.id', $userId);
                    })
                    ->orWhereHas('groups', function (Builder $query) use ($userId) {
                        $query->whereHas('users', function (Builder $query) use ($userId) {
                            $query->where('users.id', $userId);
                        });
                    });
            });
    }

    private function orderQuery(Builder $query, ?OrderDto $orderDto = null): Builder
    {
        $order = $orderDto->getOrder() ?? 'desc';

        if ($orderDto->getOrderBy() && $orderDto->getOrderBy() !== 'obtained') {
            return $query->orderBy($orderDto->getOrderBy(), $order);
        } else {
            if (DB::connection()->getDriverName() === 'pgsql') {
                $order = $order === 'desc' ? $order . ' NULLS LAST' : $order . ' NULLS FIRST';
            }
            return $query->orderByRaw("LEAST(COALESCE(user_pivot_created_at, group_pivot_created_at), COALESCE(group_pivot_created_at, user_pivot_created_at)) $order");
        }
    }

    private function applyFilters(Builder $query, int $userId, ?string $filter = null): Builder
    {
        return match ($filter) {
            ProgressFilterEnum::STARTED => $this->filterForStartedCourses($query, $userId),
            ProgressFilterEnum::FINISHED => $this->filterForFinishedCourses($query, $userId),
            ProgressFilterEnum::PLANNED => $this->filterForPlannedCourses($query, $userId),
            default => $query,
        };
    }

    private function filterForPlannedCourses(Builder $query, int $userId): Builder
    {
        return $query
            ->where(function (Builder $query) use ($userId) {
                $query
                    ->whereDoesntHave('topics.progress')
                    ->orWhereNotExists(function (QueryBuilder $query) use ($userId) {
                        $query->select(DB::raw(1))
                            ->from('topics')
                            ->join('lessons', 'lessons.id', '=', 'topics.lesson_id')
                            ->join('course_progress', 'topics.id', '=', 'course_progress.topic_id')
                            ->whereColumn('lessons.course_id', 'courses.id')
                            ->where('course_progress.user_id', $userId)
                            ->whereIn('course_progress.status', [ProgressStatus::COMPLETE, ProgressStatus::IN_PROGRESS]);
                    });
            });
    }

    private function filterForStartedCourses(Builder $query, int $userId): Builder
    {
        return $query
            ->whereExists(function (QueryBuilder $query) use ($userId) {
                $query->select(DB::raw(1))
                    ->from('topics')
                    ->join('lessons', 'lessons.id', '=', 'topics.lesson_id')
                    ->leftJoin('course_progress', function (JoinClause $join) use ($userId) {
                        $join->on('topics.id', '=', 'course_progress.topic_id')
                            ->where('course_progress.user_id', $userId);
                    })
                    ->whereColumn('lessons.course_id', 'courses.id')
                    ->where('course_progress.status', ProgressStatus::IN_PROGRESS);
            });
    }

    private function filterForFinishedCourses(Builder $query, int $userId): Builder
    {
        return $query
            ->whereNotExists(function (QueryBuilder $query) use ($userId) {
                $query->select(DB::raw(1))
                    ->from('topics')
                    ->join('lessons', 'lessons.id', '=', 'topics.lesson_id')
                    ->where('lessons.active', true)
                    ->where('topics.active', true)
                    ->whereColumn('courses.id', 'lessons.course_id')
                    ->join('course_progress', 'topics.id', '=', 'course_progress.topic_id')
                    ->where('course_progress.user_id', $userId)
                    ->whereNull('course_progress.finished_at');
            });
    }

    private function getParentGroups(Collection $groups): Collection
    {
        $childGroups = Group::query()->whereIn('id', $groups->pluck('parent_id')->unique())->get();
        if ($childGroups->count() > 0) {
            $childGroups = $childGroups->merge($this->getParentGroups($childGroups));
        }

        return $childGroups;
    }
}
