<?php

namespace EscolaLms\Courses\Services;

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
            )->with(['categories','tags', 'author'])
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
}
