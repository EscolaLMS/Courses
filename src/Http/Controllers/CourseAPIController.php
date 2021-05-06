<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Categories\Models\Category;
use EscolaLms\Categories\Repositories\Contracts\CategoriesRepositoryContract;
use EscolaLms\Courses\Http\Controllers\Swagger\CourseAPISwagger;
use EscolaLms\Courses\Http\Requests\AttachCategoriesCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\AttachTagsCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\CreateCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\GetCourseCurriculumAPIRequest;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use EscolaLms\Courses\Repositories\CourseRepository;
use EscolaLms\Courses\Services\Contracts\CourseServiceContract;
use Illuminate\Http\Request;
use Response;

/**
 * Class CourseController
 * @package App\Http\Controllers
 */
class CourseAPIController extends AppBaseController implements CourseAPISwagger
{
    /** @var  CourseRepository */
    private CourseRepositoryContract $courseRepository;
    private CourseServiceContract $courseServiceContract;
    private CategoriesRepositoryContract $categoriesRepositoryContract;

    public function __construct(
        CourseRepositoryContract $courseRepo,
        CourseServiceContract $courseServiceContract,
        CategoriesRepositoryContract $categoriesRepositoryContract
    ) {
        $this->courseRepository = $courseRepo;
        $this->courseServiceContract = $courseServiceContract;
        $this->categoriesRepositoryContract = $categoriesRepositoryContract;
    }

    public function index(Request $request)
    {
        $courses = $this->courseRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($courses->toArray(), 'Courses retrieved successfully');
    }

    public function store(CreateCourseAPIRequest $request)
    {
        $input = $request->all();

        $course = $this->courseRepository->create($input);

        return $this->sendResponse($course->toArray(), 'Course saved successfully');
    }

    public function show($id)
    {
        /** @var Course $course */
        $course = $this->courseRepository->findWith($id, ['*'], ['lessons']);

        if (empty($course)) {
            return $this->sendError('Course not found');
        }

        return $this->sendResponse($course->toArray(), 'Course retrieved successfully');
    }

    public function program($id, GetCourseCurriculumAPIRequest $request)
    {
        /** @var Course $course */
        $course = $this->courseRepository->findWith($id, ['*'], [
            'lessons.topics.topicable']);

        if (empty($course)) {
            return $this->sendError('Course not found');
        }

        return $this->sendResponse($course->toArray(), 'Course retrieved successfully');
    }

    public function update($id, UpdateCourseAPIRequest $request)
    {
        $input = $request->all();

        /** @var Course $course */
        $course = $this->courseRepository->find($id);

        if (empty($course)) {
            return $this->sendError('Course not found');
        }

        $course = $this->courseRepository->update($input, $id);

        return $this->sendResponse($course->toArray(), 'Course updated successfully');
    }

    public function destroy($id)
    {
        /** @var Course $course */
        $course = $this->courseRepository->find($id);

        if (empty($course)) {
            return $this->sendError('Course not found');
        }

        $course->delete();

        return $this->sendSuccess('Course deleted successfully');
    }

    public function category(int $category_id, Request $request)
    {
        /** @var Category $category */
        $category = $this->categoriesRepositoryContract->find($category_id);
        $courses = $this->courseServiceContract->searchInCategoryAndSubCategory($category);
        return $this->sendResponse($courses->toArray(), 'Course updated successfully');
    }

    public function attachCategory(int $id, AttachCategoriesCourseAPIRequest $attachCategoriesCourseAPIRequest)
    {
        /** @var Course $course */
        $course = $this->courseRepository->find($id);
        $this->courseServiceContract->attachCategories($course, $attachCategoriesCourseAPIRequest->input('categories'));

        return $this->sendResponse([], 'Course updated successfully');
    }

    public function attachTags(int $id, AttachTagsCourseAPIRequest $attachTagsCourseAPIRequest)
    {
        /** @var Course $course */
        $course = $this->courseRepository->find($id);

        $this->courseServiceContract->attachTags($course, $attachTagsCourseAPIRequest->input('tags'));
        return $this->sendResponse([], 'Course updated successfully');
    }

    public function searchByTag(Request $request)
    {
        $courses = $this->courseRepository
            ->allQueryBuilder($request->only('tag'))
            ->orderBy('courses.id', 'desc')
            ->paginate();
        return $this->sendResponse($courses->toArray(), 'Course updated successfully');
    }
}
