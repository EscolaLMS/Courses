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
        if ($request->has('users')) {
            $changes = $course->users()->syncWithoutDetaching($request->input('users'));
            $this->courseService->sendNotificationsForCourseAssignments($course, $changes);
        }
        if ($request->has('groups')) {
            $course->groups()->syncWithoutDetaching($request->input('groups'));
        }
        return $this->sendAccessListResponse($course, __('Added to access list'));
    }

    public function remove(int $course_id, RemoveAccessAPIRequest $request): JsonResponse
    {
        $course = $request->getCourse();
        if ($request->has('users')) {
            $course->users()->detach($request->input('users'));
            $this->courseService->sendNotificationsForCourseAssignments($course, ['detached' => $request->input('users')]);
        }
        if ($request->has('groups')) {
            $course->groups()->detach($request->input('groups'));
        }
        return $this->sendAccessListResponse($course, __('Removed from access list'));
    }

    public function set(int $course_id, SetAccessAPIRequest $request): JsonResponse
    {
        $course = $request->getCourse();
        if ($request->has('users')) {
            if (!empty($request->input('users'))) {
                $changes = $course->users()->sync($request->input('users'));
            } else {
                $changes['detached'] = $course->users;
                $course->users()->detach();
            }
            $this->courseService->sendNotificationsForCourseAssignments($course, $changes);
        }
        if ($request->has('groups')) {
            if (!empty($request->input('groups'))) {
                $course->groups()->sync($request->input('groups'));
            } else {
                $course->groups()->detach();
            }
        }
        return $this->sendAccessListResponse($course, __('Set access list'));
    }

    private function sendAccessListResponse(Course $course, string $message): JsonResponse
    {
        return $this->sendResponse([
            'users' => UserShortResource::collection($course->users),
            'groups' => UserGroupResource::collection($course->groups),
        ], $message);
    }
}
