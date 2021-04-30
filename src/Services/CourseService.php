<?php


namespace EscolaLms\Courses\Services;


use EscolaLms\Categories\Models\Category;
use EscolaLms\Categories\Repositories\Criteria\CourseInCategory;
use EscolaLms\Core\Dtos\PaginationDto;
use EscolaLms\Core\Repositories\Criteria\CourseSearch;
use EscolaLms\Courses\Dto\CourseSearchDto;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use EscolaLms\Courses\Services\Contracts\CourseServiceContract;
use EscolaLms\Tags\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
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

    /**
     * @param Course $course
     * @param array $categories
     */
    public function attachCategories(Course $course, array $categories)
    {
        $categoriesCollection = Category::whereIn('id', $categories)->get();
        if ($categoriesCollection) {
            $courseCategoriesIds = $course->categories()->get()->pluck('id')->toArray();
            foreach ($categoriesCollection as $category) {
                if (!in_array($category->getKey(), $courseCategoriesIds)) {
                    if (!$this->courseRepository->attachCategory($course, $category)) {
                        abort(422, 'Operation failed');
                    }
                }
            }
        }
    }

    /**
     * @param Course $course
     * @param array $tags
     */
    public function attachTags(Course $course, array $tags)
    {
        foreach ($tags as $tag) {
            $courseTagsIds = $course->tags()->get()->pluck('title')->toArray();
            if (!in_array($tag['title'], $courseTagsIds)) {
                $tagModel = new Tag($tag);
                if (!$this->courseRepository->attachTag($course, $tagModel)) {
                    abort(422, 'Operation failed');
                }
            }
        }
    }

}