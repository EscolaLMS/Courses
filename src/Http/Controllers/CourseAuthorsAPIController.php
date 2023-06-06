<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Auth\Dtos\Admin\UserAssignableDto;
use EscolaLms\Auth\Http\Resources\UserFullResource;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Courses\Enum\CoursesPermissionsEnum;
use EscolaLms\Courses\Http\Controllers\Swagger\CourseAuthorsAPISwagger;
use EscolaLms\Courses\Http\Requests\AssignAuthorApiRequest;
use EscolaLms\Courses\Http\Requests\CourseAssignableUserListRequest;
use EscolaLms\Courses\Http\Resources\TutorResource;
use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseAuthorsAPIController extends AppBaseController implements CourseAuthorsAPISwagger
{
    protected CourseRepositoryContract $courseRepositoryContract;
    protected UserServiceContract $userService;

    public function __construct(
        CourseRepositoryContract $courseRepositoryContract,
        UserServiceContract $userService
    ) {
        $this->courseRepositoryContract = $courseRepositoryContract;
        $this->userService = $userService;
    }

    public function index(Request $request): JsonResponse
    {
        $tutors = $this->courseRepositoryContract->findTutors();

        return $this->sendResponseForResource(TutorResource::collection($tutors), __('Tutors retrieved successfully'));
    }

    /**
     * Display the specified CourseProgress.
     */
    public function show($id, Request $request): JsonResponse
    {
        $tutor = $this->courseRepositoryContract->findTutor($id);

        if (empty($tutor)) {
            return $this->sendError('Not found', 404);
        }

        return $this->sendResponseForResource(TutorResource::make($tutor), __('Tutor retrieved successfully'));
    }

    public function assign(AssignAuthorApiRequest $request): JsonResponse
    {
        $tutor = $request->getTutor();
        $course = $request->getCourse();

        if (empty($tutor)) {
            return $this->sendError(__('Tutor not found'), 404);
        }
        if (empty($course)) {
            return $this->sendError(__('Course not found'), 404);
        }

        $this->courseRepositoryContract->addAuthor($course, $tutor);

        return $this->sendResponse(TutorResource::collection($course->refresh()->authors), __('Tutor assigned'));
    }

    public function unassign(AssignAuthorApiRequest $request): JsonResponse
    {
        $tutor = $request->getTutor();
        $course = $request->getCourse();

        if (empty($tutor)) {
            return $this->sendError(__('Tutor not found'), 404);
        }
        if (empty($course)) {
            return $this->sendError(__('Course not found'), 404);
        }

        $this->courseRepositoryContract->removeAuthor($course, $tutor);

        return $this->sendResponse(TutorResource::collection($course->refresh()->authors), __('Tutor unassigned'));
    }

    public function assignableUsers(CourseAssignableUserListRequest $request): JsonResponse
    {
        $dto = UserAssignableDto::instantiateFromArray(array_merge($request->validated(), ['assignable_by' => CoursesPermissionsEnum::COURSE_CREATE]));
        $result = $this->userService
            ->assignableUsers($dto, $request->get('per_page'), $request->get('page'));
        return $this->sendResponseForResource(UserFullResource::collection($result), __('Users assignable to courses'));
    }
}
