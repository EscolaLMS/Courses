<?php

namespace EscolaLms\Courses\Services;

use Error;
use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Core\Models\User;
use EscolaLms\Core\Repositories\Criteria\Primitives\EqualCriterion;
use EscolaLms\Courses\Events\EscolaLmsCourseAccessStartedTemplateEvent;
use EscolaLms\Courses\Events\EscolaLmsCourseAssignedTemplateEvent;
use EscolaLms\Courses\Events\EscolaLmsCourseFinishedTemplateEvent;
use EscolaLms\Courses\Events\EscolaLmsCourseUnassignedTemplateEvent;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use EscolaLms\Courses\Repositories\Criteria\CourseSearch;
use EscolaLms\Courses\Repositories\Criteria\Primitives\OrderCriterion;
use EscolaLms\Courses\Services\Contracts\CourseServiceContract;
use EscolaLms\Scorm\Services\Contracts\ScormServiceContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Peopleaps\Scorm\Model\ScormScoModel;

class CourseService implements CourseServiceContract
{
    private CourseRepositoryContract $courseRepository;

    public function __construct(
        CourseRepositoryContract $courseRepository,
        ScormServiceContract $scormService
    ) {
        $this->courseRepository = $courseRepository;
        $this->scormService = $scormService;
    }

    public function getCoursesListWithOrdering(OrderDto $orderDto, array $search = []): Builder
    {
        $criteria = $this->prepareCriteria($orderDto);

        if (isset($search['title'])) {
            $criteria[] = new CourseSearch($search['title']);
            unset($search['title']);
        }
        if (isset($search['free']) && $search['free']) {
            $criteria[] = new EqualCriterion('base_price', 0);
            unset($search['free']);
        }

        $query = $this->courseRepository->allQueryBuilder(
            $search,
            $criteria
        )->with(['categories', 'tags', 'authors'])
            ->withCount(['lessons', 'users', 'topics', 'authors']);

        return $query;
    }

    /**
     * @param OrderDto $orderDto
     * @return array
     */
    private function prepareCriteria(OrderDto $orderDto): array
    {
        $criteria = [];

        if (!is_null($orderDto->getOrder())) {
            $criteria[] = new OrderCriterion($orderDto->getOrderBy(), $orderDto->getOrder());
        }
        return $criteria;
    }

    public function sort($class, $orders): void
    {
        if ($class === 'Lesson') {
            foreach ($orders as $order) {
                Lesson::findOrFail($order[0])->update(['order' => $order[1]]);
            }
        }
        if ($class === 'Topic') {
            foreach ($orders as $order) {
                Topic::findOrFail($order[0])->update(['order' => $order[1]]);
            }
        }
    }

    public function getScormPlayer(int $courseId)
    {
        $course = Course::with(['scormSco'])->findOrFail($courseId);

        if (empty($course->scorm_sco_id)) {
            throw new Error("This course does not have SCORM SCO object!");
        }

        $sco = ScormScoModel::where('id', $course->scorm_sco_id)->first();
        return $this->scormService->getScoViewDataByUuid($sco->uuid);
    }

    public function addAccessForUsers(Course $course, array $users = []): void
    {
        if (!empty($users)) {
            $changes = $course->users()->syncWithoutDetaching($users);
            $this->dispatchEventForUsersAttachedToCourse($course, $changes['attached']);
        }
    }

    public function addAccessForGroups(Course $course, array $groups = []): void
    {
        if (!empty($groups)) {
            $course->groups()->syncWithoutDetaching($groups);
        }
    }

    public function removeAccessForUsers(Course $course, array $users = []): void
    {
        if (!empty($users)) {
            $course->users()->detach($users);
            $this->dispatchEventForUsersDetachedFromCourse($course, $users);
        }
    }

    public function removeAccessForGroups(Course $course, array $groups = []): void
    {
        if (!empty($groups)) {
            $course->groups()->detach($groups);
        }
    }

    public function setAccessForUsers(Course $course, array $users = []): void
    {
        $changes = $course->users()->sync($users);
        $this->dispatchEventForUsersAttachedToCourse($course, $changes['attached']);
        $this->dispatchEventForUsersDetachedFromCourse($course, $changes['detached']);
    }

    public function setAccessForGroups(Course $course, array $groups = []): void
    {
        $course->groups()->sync($groups);
    }

    private function dispatchEventForUsersAttachedToCourse(Course $course, array $users = []): void
    {
        foreach ($users as $attached) {
            /** @var User $user */
            $user = is_int($attached) ? User::find($attached) : $attached;
            if ($user) {
                event(new EscolaLmsCourseAssignedTemplateEvent($user, $course));
                event(new EscolaLmsCourseAccessStartedTemplateEvent($user, $course));
            }
        }
    }

    private function dispatchEventForUsersDetachedFromCourse(Course $course, array $users = []): void
    {
        foreach ($users as $detached) {
            /** @var User $user */
            $user = is_int($detached) ? User::find($detached) : $detached;
            if ($user) {
                event(new EscolaLmsCourseUnassignedTemplateEvent($user, $course));
                event(new EscolaLmsCourseFinishedTemplateEvent($user, $course));
            }
        }
    }
}
