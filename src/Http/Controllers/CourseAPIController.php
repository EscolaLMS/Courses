<?php

namespace EscolaLms\Courses\Http\Controllers;

use Error;
use EscolaLms\Categories\Repositories\Contracts\CategoriesRepositoryContract;
use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Courses\Exceptions\TopicException;
use EscolaLms\Courses\Http\Controllers\Swagger\CourseAPISwagger;
use EscolaLms\Courses\Http\Requests\CreateCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\DeleteCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\GetCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\GetCourseCurriculumAPIRequest;
use EscolaLms\Courses\Http\Requests\SortAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateCourseAPIRequest;
use EscolaLms\Courses\Http\Resources\CourseSimpleResource;
use EscolaLms\Courses\Http\Resources\CourseWithProgramAdminResource;
use EscolaLms\Courses\Http\Resources\CourseWithProgramResource;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use EscolaLms\Courses\Repositories\CourseRepository;
use EscolaLms\Courses\Services\Contracts\CourseServiceContract;
use Illuminate\Http\Request;
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

        $user = $request->user();
        if (!isset($user) || !$user->hasRole([UserRole::ADMIN, UserRole::TUTOR])) {
            $search['active'] = true;
        }

        $orderDto = OrderDto::instantiateFromRequest($request);

        $courses = $this->courseServiceContract->getCoursesListWithOrdering($orderDto, $search)->paginate($request->get('per_page') ?? 15);

        return $this->sendResponseForResource(CourseSimpleResource::collection($courses), 'Courses retrieved successfully');
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

        return $this->sendResponseForResource(CourseSimpleResource::make($course), 'Course saved successfully');
    }

    public function show($id, GetCourseAPIRequest $request)
    {
        $course = $request->getCourse();

        if (empty($course)) {
            return $this->sendError('Course not found');
        }

        return $this->sendResponseForResource(CourseSimpleResource::make($course->loadMissing('lessons', 'lessons.topics', 'lessons.topics.topicable', 'categories', 'tags', 'author')->loadCount('users')), 'Course retrieved successfully');
    }

    public function program($id, GetCourseCurriculumAPIRequest $request)
    {
        /** @var Course $course */
        try {
            $course = $this->courseRepository->findWith($id, ['*'], ['lessons.topics.topicable', 'scorm.scos']);
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

        $resource = ($request->user() && $request->user()->can('update', $course)) ? CourseWithProgramAdminResource::make($course) : CourseWithProgramResource::make($course);
        return $this->sendResponseForResource($resource, 'Course retrieved successfully');
    }

    public function scorm($id, GetCourseCurriculumAPIRequest $request)
    {

        try {
            $player = $this->courseServiceContract->getScormPlayer($id);
        } catch (Error $error) {
            return $this->sendError($error->getMessage(), 422);
        }

        return $player;
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

        return $this->sendResponseForResource(CourseSimpleResource::make($course), 'Course updated successfully');
    }

    public function destroy($id, DeleteCourseAPIRequest $request)
    {
        $course = $request->getCourse();

        if (empty($course)) {
            return $this->sendError('Course not found');
        }

        try {
            $this->courseRepository->delete($id);
        } catch (AccessDeniedHttpException $error) {
            return $this->sendError($error->getMessage(), 403);
        } catch (TopicException $error) {
            return $this->sendDataError($error->getMessage(), $error->getData());
        } catch (Error $error) {
            return $this->sendError($error->getMessage(), 422);
        }

        return $this->sendSuccess('Course deleted successfully');
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
        return $this->sendResponse([], $request->get('class') . ' sorted successfully');
    }
}
