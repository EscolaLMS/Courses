<?php

namespace EscolaLms\Courses\Services;

use Carbon\Carbon;
use Error;
use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Core\Repositories\Criteria\Primitives\DateCriterion;
use EscolaLms\Core\Repositories\Criteria\Primitives\EqualCriterion;
use EscolaLms\Core\Repositories\Criteria\Primitives\HasCriterion;
use EscolaLms\Core\Repositories\Criteria\Primitives\InCriterion;
use EscolaLms\Core\Repositories\Criteria\Primitives\WhereCriterion;
use EscolaLms\Courses\Enum\CourseStatusEnum;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use EscolaLms\Courses\Repositories\Criteria\CourseSearch;
use EscolaLms\Courses\Repositories\Criteria\Primitives\OrderCriterion;
use EscolaLms\Courses\Services\Contracts\CourseServiceContract;
use EscolaLms\Scorm\Services\Contracts\ScormServiceContract;
use Illuminate\Database\Eloquent\Builder;
use Peopleaps\Scorm\Model\ScormScoModel;

class CourseService implements CourseServiceContract
{
    private CourseRepositoryContract $courseRepository;
    private ScormServiceContract $scormService;

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
        if (isset($search['status']) && is_array($search['status'])) {
            $criteria[] = new InCriterion('status', $search['status']);
            unset($search['status']);
        }
        if (isset($search['only_with_categories']) && $search['only_with_categories'] === 'true') {
            $criteria[] = new HasCriterion('categories', null);
            unset($search['only_with_categories']);
        }
        if (isset($search['authors']) && is_array($search['authors'])) {
            $criteria[] = new HasCriterion('authors', fn($query) => $query->whereIn('author_id', $search['authors']));
            unset($search['authors']);
        }
        if (isset($search['group_id'])) {
            $criteria[] = new HasCriterion('groups', fn($query) => $query->where('group_id', $search['group_id']));
            unset($search['group_id']);
        }
        if (isset($search['no_expired']) && $search['no_expired']) {
            $criteria[] = new WhereCriterion('active_to', now(), '>=');
            unset($search['no_expired']);
        }

        $query = $this->courseRepository->allQueryBuilder(
            $search,
            $criteria
        )
            ->with([
                'categories',
                'tags',
                'authors',
                'authors.interests',
            ])
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

    public function activatePublishedCourses(): void
    {
        $criteria = [
            new EqualCriterion('status', CourseStatusEnum::PUBLISHED_UNACTIVATED),
            new DateCriterion('active_from', Carbon::now(), '<='),
            new DateCriterion('active_to', Carbon::now(), '>')
        ];

        $unactivatedCourses = $this->courseRepository->searchByCriteria($criteria);

        $unactivatedCourses->each(function (Course $course) {
           $this->courseRepository->update(['status' => CourseStatusEnum::PUBLISHED], $course->getKey());
        });
    }
}
