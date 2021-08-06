<?php

namespace EscolaLms\Courses\Services;

use Error;
use EscolaLms\Categories\Models\Category;
use EscolaLms\Categories\Repositories\Criteria\CourseInCategory;
use EscolaLms\Core\Dtos\PaginationDto;
use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Courses\Dto\CourseSearchDto;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;

use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use EscolaLms\Courses\Services\Contracts\CourseServiceContract;
use EscolaLms\Courses\Repositories\Criteria\Primitives\OrderCriterion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use EscolaLms\Tags\Models\Tag;
use Illuminate\Support\Collection;
use EscolaLms\Courses\Repositories\Criteria\CourseSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;
use EscolaLms\Scorm\Services\Contracts\ScormServiceContract;

class CourseService implements CourseServiceContract
{
    private CourseRepositoryContract $courseRepository;

    public function __construct(
        CourseRepositoryContract $courseRepository
    ) {
        $this->courseRepository = $courseRepository;
    }

    public function getCoursesListWithOrdering(OrderDto $orderDto, PaginationDto $paginationDto, array $search = []): Builder
    {
        $criteria = $this->prepareCriteria($orderDto);

        if (isset($search['title'])) {
            $criteria[] = new CourseSearch($search['title']);
            unset($search['title']);
        }

        $query = $this->courseRepository
            ->allQueryBuilder(
                $search,
                $paginationDto->getSkip(),
                $paginationDto->getLimit(),
                $criteria
            )->with(['categories', 'tags', 'author'])
            ->withCount(['lessons', 'users', 'topic']);

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

    public function getScormPlayer($courseId)
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

        $scormService = App::make(ScormServiceContract::class);

        $data = $scormService->getScoByUuid($uuid);
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
