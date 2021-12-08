<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\Courses\Http\Controllers\Swagger\CoursesAccessAPISwagger;
use EscolaLms\Courses\Http\Requests\AddAccessAPIRequest;
use EscolaLms\Courses\Http\Requests\ListAccessAPIRequest;
use EscolaLms\Courses\Http\Requests\RemoveAccessAPIRequest;
use EscolaLms\Courses\Http\Requests\SetAccessAPIRequest;
use EscolaLms\Courses\Http\Resources\UserGroupResource;
use EscolaLms\Courses\Http\Resources\UserShortResource;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Services\Contracts\CourseServiceContract;
use Illuminate\Http\JsonResponse;

class CourseAccessAPIController extends EscolaLmsBaseController implements CoursesAccessAPISwagger
{
    private CourseServiceContract $courseService;
    public function __construct(CourseServiceContract $courseService)
    {
        $this->courseService = $courseService;
    }

    public function list(int $course_id, ListAccessAPIRequest $request): JsonResponse
    {
        $course = $request->getCourse();
        return $this->sendAccessListResponse($course, __('Access List'));
    }

    public function add(int $course_id, AddAccessAPIRequest $request): JsonResponse
    {
        $course = $request->getCourse();
        $this->courseService->addAccessForUsers($course, $request->input('users', []));
        $this->courseService->addAccessForGroups($course, $request->input('groups', []));
        return $this->sendAccessListResponse($course->refresh(), __('Added to access list'));
    }

    public function remove(int $course_id, RemoveAccessAPIRequest $request): JsonResponse
    {
        $course = $request->getCourse();
        $this->courseService->removeAccessForUsers($course, $request->input('users', []));
        $this->courseService->removeAccessForGroups($course, $request->input('groups', []));
        return $this->sendAccessListResponse($course->refresh(), __('Removed from access list'));
    }

    public function set(int $course_id, SetAccessAPIRequest $request): JsonResponse
    {
        $course = $request->getCourse();
        if ($request->has('users')) {
            $this->courseService->setAccessForUsers($course, $request->input('users'));
        }
        if ($request->has('groups')) {
            $this->courseService->setAccessForGroups($course, $request->input('groups'));
        }
        return $this->sendAccessListResponse($course->refresh(), __('Set access list'));
    }

    private function sendAccessListResponse(Course $course, string $message): JsonResponse
    {
        return $this->sendResponse([
            'users' => UserShortResource::collection($course->users),
            'groups' => UserGroupResource::collection($course->groups),
        ], $message);
    }
}
