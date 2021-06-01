<?php

namespace EscolaLms\Courses\Services;

use EscolaLms\Categories\Models\Category;
use EscolaLms\Categories\Repositories\Criteria\CourseInCategory;
use EscolaLms\Core\Dtos\PaginationDto;
use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Courses\Dto\CourseSearchDto;
use EscolaLms\Courses\Models\Course;
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

    public function searchInCategory(CourseSearchDto $courseSearchDto, Category $category): LengthAwarePaginator
    {
        $criteria = [
            new CourseSearch($courseSearchDto->getQuery()),
            new CourseInCategory($category),
        ];

        return $this->getCoursesListByCriteria($criteria);
    }

  

    public function getCoursesListByCriteria(array $criteria, ?PaginationDto $pagination = null): LengthAwarePaginator
    {
        if (is_null($pagination)) {
            $page = config('app.paginate_count');
        } else {
            $page = $pagination->getPage();
        }
        $query = $this->courseRepository->queryAll()->orderBy('id', 'desc');
        $courses = $this->courseRepository
            ->applyCriteria($query, $criteria)
            ->paginate();
        return $courses;
    }

    public function attachCategories(Course $course, array $categories)
    {
        $categoriesCollection = Category::whereIn('id', $categories)->get();
        if ($categoriesCollection) {
            $courseCategoriesIds = $course->categories()->get()->pluck('id')->toArray();
            foreach ($categoriesCollection as $category) {
                // If the category is already assigned to the course, skip loop
                if (in_array($category->getKey(), $courseCategoriesIds)) {
                    continue;
                }

                // If the category cannot be assigned to the course, stop action
                if (!$this->courseRepository->attachCategory($course, $category)) {
                    abort(422, 'Operation failed');
                }
            }
        }
    }

    public function attachTags(Course $course, array $tags)
    {
        foreach ($tags as $tag) {
            $courseTagsIds = $course->tags()->get()->pluck('title')->toArray();
            // If the tag is already assigned to the course, skip loop
            if (in_array($tag['title'], $courseTagsIds)) {
                continue;
            }

            $tagModel = new Tag($tag);
            // If the tag cannot be assigned to the course, stop action
            if (!$this->courseRepository->attachTag($course, $tagModel)) {
                abort(422, 'Operation failed');
            }
        }
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
            )->with(['categories','tags']);

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
}
