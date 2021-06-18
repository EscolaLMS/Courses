<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Categories\Models\Category;
use EscolaLms\Categories\Repositories\Contracts\CategoriesRepositoryContract;
use EscolaLms\Courses\Http\Controllers\Swagger\CourseAPISwagger;
use EscolaLms\Courses\Http\Requests\AttachCategoriesCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\AttachTagsCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\CreateCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\DeleteCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\GetCourseCurriculumAPIRequest;
use EscolaLms\Courses\Http\Requests\SortAPIRequest;
use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Core\Dtos\PaginationDto;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use EscolaLms\Courses\Repositories\CourseRepository;
use EscolaLms\Courses\Services\Contracts\CourseServiceContract;
use Illuminate\Http\Request;
use Response;
use EscolaLms\Courses\Exceptions\TopicException;
use Error;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
        $search = $request->except(['limit', 'skip', 'order', 'order_by']);

        $orderDto = OrderDto::instantiateFromRequest($request);

        $courses = $this->courseServiceContract->getCoursesListWithOrdering(
            $orderDto,
            PaginationDto::instantiateFromRequest($request),
            $search
        )->paginate($request->get('per_page') ?? 15);

        return $this->sendResponse($courses->toArray(), 'Courses retrieved successfully');
    }

    public function store(CreateCourseAPIRequest $request)
    {
        $input = $request->all();

        try {
            $course = $this->courseRepository->create($input);
        } catch (AccessDeniedHttpException $error) {
            return $this->sendError($error->getMessage(), 403);
        } catch (TopicException $error) {
            return $this->sendDataError($error->getMessage(), $error->getData());
        } catch (Error $error) {
            return $this->sendError($error->getMessage(), 422);
        }

        return $this->sendResponse($course->toArray(), 'Course saved successfully');
    }

    public function show($id)
    {
        /** @var Course $course */
        $course = $this->courseRepository->findWith($id, ['*'], ['lessons.topics.topicable', 'categories', 'tags']);

        if (empty($course)) {
            return $this->sendError('Course not found');
        }

        return $this->sendResponse($course->toArray(), 'Course retrieved successfully');
    }

    public function program($id, GetCourseCurriculumAPIRequest $request)
    {
        /** @var Course $course */
        try {
            $course = $this->courseRepository->findWith($id, ['*'], ['lessons.topics.topicable']);
        } catch (AccessDeniedHttpException $error) {
            return $this->sendError($error->getMessage(), 403);
        } catch (TopicException $error) {
            return $this->sendDataError($error->getMessage(), $error->getData());
        } catch (Error $error) {
            return $this->sendError($error->getMessage(), 422);
        }

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

        try {
            $course = $this->courseRepository->update($input, $id);
            $course->load(['lessons.topics.topicable', 'categories', 'tags']);
        } catch (AccessDeniedHttpException $error) {
            return $this->sendError($error->getMessage(), 403);
        } catch (TopicException $error) {
            return $this->sendDataError($error->getMessage(), $error->getData());
        } catch (Error $error) {
            return $this->sendError($error->getMessage(), 422);
        }

        return $this->sendResponse($course->toArray(), 'Course updated successfully');
    }

    public function destroy($id, DeleteCourseAPIRequest $request)
    {
        /** @var Course $course */
        $course = $this->courseRepository->find($id);

        if (empty($course)) {
            return $this->sendError('Course not found');
        }

        try {
            $course->delete();
        } catch (AccessDeniedHttpException $error) {
            return $this->sendError($error->getMessage(), 403);
        } catch (TopicException $error) {
            return $this->sendDataError($error->getMessage(), $error->getData());
        } catch (Error $error) {
            return $this->sendError($error->getMessage(), 422);
        }

        return $this->sendSuccess('Course deleted successfully');
    }

    public function attachCategory(int $id, AttachCategoriesCourseAPIRequest $attachCategoriesCourseAPIRequest)
    {
        /** @var Course $course */
        $course = $this->courseRepository->find($id);
        try {
            $this->courseServiceContract->attachCategories($course, $attachCategoriesCourseAPIRequest->input('categories'));
        } catch (AccessDeniedHttpException $error) {
            return $this->sendError($error->getMessage(), 403);
        } catch (TopicException $error) {
            return $this->sendDataError($error->getMessage(), $error->getData());
        } catch (Error $error) {
            return $this->sendError($error->getMessage(), 422);
        }

        return $this->sendResponse([], 'Course updated successfully');
    }

    public function attachTags(int $id, AttachTagsCourseAPIRequest $attachTagsCourseAPIRequest)
    {
        /** @var Course $course */
        $course = $this->courseRepository->find($id);

        try {
            $this->courseServiceContract->attachTags($course, $attachTagsCourseAPIRequest->input('tags'));
        } catch (AccessDeniedHttpException $error) {
            return $this->sendError($error->getMessage(), 403);
        } catch (TopicException $error) {
            return $this->sendDataError($error->getMessage(), $error->getData());
        } catch (Error $error) {
            return $this->sendError($error->getMessage(), 422);
        }
        return $this->sendResponse([], 'Course updated successfully');
    }

    public function sort(SortAPIRequest $request)
    {
        try {
            $this->courseServiceContract->sort($request->get('class'), $request->get('orders'));
        } catch (AccessDeniedHttpException $error) {
            return $this->sendError($error->getMessage(), 403);
        } catch (TopicException $error) {
            return $this->sendDataError($error->getMessage(), $error->getData());
        } catch (Error $error) {
            return $this->sendError($error->getMessage(), 422);
        }
        return $this->sendResponse([], $request->get('class'). ' sorted successfully');
    }
}
