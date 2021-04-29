<?php


namespace EscolaLms\Courses\Services;


use EscolaLms\Categories\Models\Category;
use EscolaLms\Categories\Repositories\Criteria\CourseInCategory;
use EscolaLms\Core\Dtos\PaginationDto;
use EscolaLms\Core\Repositories\Criteria\CourseSearch;
use EscolaLms\Courses\Dto\CourseSearchDto;
use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use EscolaLms\Courses\Services\Contracts\CourseServiceContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CourseService implements CourseServiceContract
{
    private CourseRepositoryContract $courseRepository;

    public function __construct(
        CourseRepositoryContract $courseRepository
    )
    {
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

    public function searchInCategoryAndSubCategory(Category $category): LengthAwarePaginator
    {
        $search = [
            'category_id' => $category->getKey()
        ];
        $courses = $this->courseRepository
            ->allQueryBuilder($search)
            ->orderBy('courses.id', 'desc')
            ->paginate();
        return $courses;

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

}