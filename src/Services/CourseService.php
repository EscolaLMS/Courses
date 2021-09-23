<?php

namespace EscolaLms\Courses\Services;

use Error;
use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Core\Dtos\PaginationDto;
use EscolaLms\Core\Repositories\Criteria\Primitives\EqualCriterion;
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
        )->with(['categories', 'tags', 'author'])
            ->withCount(['lessons', 'users', 'topics']);

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

    public function sort($class, $orders)
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
        $course = Course::with(['scorm.scos'])->findOrFail($courseId);

        if (empty($course->scorm_id)) {
            throw new Error("This course does not have SCORM package!");
        }

        $uuid = false;
        foreach ($course->scorm->scos as $sco) {
            if (!empty($sco->entry_url)) {
                $uuid = $sco->uuid;
                break;
            }
        }

        if (!$uuid) {
            throw new Error("This course does not have SCORM entry_url");
        }

        //$scormService = App::make(ScormServiceContract::class);

        $data = $this->scormService->getScoByUuid($uuid);
        $data['entry_url_absolute'] = Storage::url('scorm/' . $data->scorm->version . '/' . $data->scorm->uuid . '/' . $data->entry_url);

        $data['player'] = (object) [
            'lmsCommitUrl' => '/api/lms',
            'logLevel' => 1,
            'autoProgress' => true,
            'cmi' => [] // cmi is user progress
        ];

        return view('scorm::player', ['data' => $data]);
    }
}
