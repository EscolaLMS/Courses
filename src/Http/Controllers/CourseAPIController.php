<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Courses\Enum\CoursesPermissionsEnum;
use EscolaLms\Courses\Enum\CourseStatusEnum;
use EscolaLms\Courses\Http\Controllers\Swagger\CourseAPISwagger;
use EscolaLms\Courses\Http\Requests\CreateCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\DeleteCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\GetCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\GetCourseCurriculumAPIRequest;
use EscolaLms\Courses\Http\Requests\ListCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\SortAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateCourseAPIRequest;
use EscolaLms\Courses\Http\Resources\Admin\CourseWithProgramAdminResource;
use EscolaLms\Courses\Http\Resources\CourseListResource;
use EscolaLms\Courses\Http\Resources\CourseSimpleResource;
use EscolaLms\Courses\Http\Resources\CourseWithProgramResource;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use EscolaLms\Courses\Repositories\CourseRepository;
use EscolaLms\Courses\Services\Contracts\CourseServiceContract;
use EscolaLms\Tags\Repository\Contracts\TagRepositoryContract;
use Illuminate\Http\JsonResponse;

/**
 * Class CourseController.
 */
class CourseAPIController extends AppBaseController implements CourseAPISwagger
{
    /** @var CourseRepository */
    private CourseRepositoryContract $courseRepository;
    private CourseServiceContract $courseServiceContract;
    private TagRepositoryContract $tagRepositoryContract;

    public function __construct(
        CourseRepositoryContract $courseRepo,
        CourseServiceContract $courseServiceContract,
        TagRepositoryContract $tagRepositoryContract
    ) {
        $this->courseRepository = $courseRepo;
        $this->courseServiceContract = $courseServiceContract;
        $this->tagRepositoryContract = $tagRepositoryContract;
    }

    public function index(ListCourseAPIRequest $request): JsonResponse
    {
        $search = $request->except(['limit', 'skip', 'order', 'order_by']);

        $user = $request->user();
        if (!isset($user) || !$user->can('create', Course::class)) {
            $search['status'] = [CourseStatusEnum::PUBLISHED, CourseStatusEnum::PUBLISHED_UNACTIVATED];
            $search['findable'] = true;
        }

        if ($user && $user->can(CoursesPermissionsEnum::COURSE_LIST_OWNED)) {
            $search['authors'][] = $user->getKey();
        }

        $orderDto = OrderDto::instantiateFromRequest($request);

        $courses = $this->courseServiceContract->getCoursesListWithOrdering($orderDto, $search)
            ->paginate($request->get('per_page') ?? 15);

        return $this->sendResponseForResource(CourseListResource::collection($courses), __('Courses retrieved successfully'));
    }

    public function store(CreateCourseAPIRequest $request): JsonResponse
    {
        $input = $request->all();
        $course = $this->courseRepository->create($input);

        return $this->sendResponseForResource(CourseSimpleResource::make($course), __('Course saved successfully'));
    }

    public function show($id, GetCourseAPIRequest $request): JsonResponse
    {
        $course = $request->getCourse();

        if (empty($course)) {
            return $this->sendError(__('Course not found'));
        }

        return $this->sendResponseForResource(
            CourseSimpleResource::make(
                $course
                    ->loadMissing('lessons', 'lessons.topics', 'lessons.topics.topicable', 'lessons.topics.resources', 'categories', 'tags', 'authors')
                    ->loadCount('users', 'authors')
            ),
            __('Course retrieved successfully')
        );
    }

    public function program($id, GetCourseCurriculumAPIRequest $request): JsonResponse
    {
        $course = $this->courseRepository->findWith(
            $id,
            ['*'],
            ['lessons.topics.topicable', 'lessons.topics.topicable.topic', 'lessons.topics.resources']
        );

        if (!$course->is_active && !$request->user()->can('update', $course)) {
            return $this->sendError(__('Course is not activated yet.'));
        }

        $resource = ($request->user() && $request->user()->can('update', $course))
            ? CourseWithProgramAdminResource::make($course)
            : (($request->user() && $course->hasUser($request->user()))
                ? CourseWithProgramResource::make($course)
                : CourseSimpleResource::make($course));

        return $this->sendResponseForResource($resource, __('Course retrieved successfully'));
    }

    public function scorm($id, GetCourseCurriculumAPIRequest $request)
    {
        $data = $this->courseServiceContract->getScormPlayer($id);

        return view('scorm::player', ['data' => $data]);
    }

    public function update($id, UpdateCourseAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var Course $course */
        $course = $this->courseRepository->find($id);

        if (empty($course)) {
            return $this->sendError(__('Course not found'));
        }

        $course = $this->courseRepository->update($input, $id);
        $course->load(['lessons.topics.topicable', 'categories', 'tags']);

        return $this->sendResponseForResource(CourseSimpleResource::make($course), __('Course updated successfully'));
    }

    public function destroy($id, DeleteCourseAPIRequest $request): JsonResponse
    {
        $course = $request->getCourse();

        if (empty($course)) {
            return $this->sendError(__('Course not found'));
        }

        $this->courseRepository->delete($id);

        return $this->sendSuccess(__('Course deleted successfully'));
    }

    public function sort(SortAPIRequest $request): JsonResponse
    {
        $this->courseServiceContract->sort($request->get('class'), $request->get('orders'));

        return $this->sendResponse([], __($request->get('class') . ' sorted successfully'));
    }

    public function uniqueTags(): JsonResponse
    {
        $tags = $this->tagRepositoryContract->uniqueTagsFromActiveCourses();
        return $tags ?
            $this->sendResponse($tags, 'Tags unique fetched successfully') :
            $this->sendError('Tags not found', 404);
    }
}
