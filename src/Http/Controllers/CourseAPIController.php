<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Categories\Models\Category;
use EscolaLms\Categories\Repositories\Contracts\CategoriesRepositoryContract;
use EscolaLms\Courses\Http\Controllers\Swagger\CourseAPISwagger;
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
use EscolaLms\Courses\Http\Requests\GetCourseAPIRequest;
use EscolaLms\Courses\Http\Resources\CourseWithProgramAdminResource;
use EscolaLms\Courses\Http\Resources\CourseWithProgramResource;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Support\Facades\Auth;


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
        $user = Auth::user();

        if (isset($user) && $user->hasRole(['admin', 'tutor'])) {
            if (isset($search['active'])) {
                $search['active'] = $search['active'];
            }
        } else {
            $search['active'] = true;
        }

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

    public function show($id, GetCourseAPIRequest $request)
    {
        $course = $request->getCourse();

        if (empty($course)) {
            return $this->sendError('Course not found');
        }

        return $this->sendResponse($course->loadMissing('lessons', 'lessons.topics', 'lessons.topics.topicable', 'categories', 'tags', 'author')->loadCount('users')->toArray(), 'Course retrieved successfully');
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
        return $this->sendResponse($resource->toArray($request), 'Course retrieved successfully');
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
