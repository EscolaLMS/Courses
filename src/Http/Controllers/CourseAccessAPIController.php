<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Auth\Http\Resources\UserGroupResource;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\Courses\Http\Requests\AddAccessAPIRequest;
use EscolaLms\Courses\Http\Requests\ListAccessAPIRequest;
use EscolaLms\Courses\Http\Requests\RemoveAccessAPIRequest;
use EscolaLms\Courses\Http\Requests\SetAccessApiRequest;
use EscolaLms\Courses\Http\Resources\UserShortResource;
use EscolaLms\Courses\Models\Course;
use Illuminate\Http\JsonResponse;

class CourseAccessAPIController extends EscolaLmsBaseController
{
    public function list(int $course_id, ListAccessAPIRequest $request): JsonResponse
    {
        $course = $request->getCourse();
        return $this->sendAccessListResponse($course, __('Access List'));
    }

    public function add(int $course_id, AddAccessAPIRequest $request)
    {
        $course = $request->getCourse();
        if ($request->has('users')) {
            $course->users()->syncWithoutDetaching($request->input('users'));
        }
        if ($request->has('groups')) {
            $course->groups()->syncWithoutDetaching($request->input('groups'));
        }
        return $this->sendAccessListResponse($course, __('Added to access list'));
    }

    public function remove(int $course_id, RemoveAccessAPIRequest $request)
    {
        $course = $request->getCourse();
        if ($request->has('users')) {
            $course->users()->detach($request->input('users'));
        }
        if ($request->has('groups')) {
            $course->groups()->detach($request->input('groups'));
        }
        return $this->sendAccessListResponse($course, __('Removed from access list'));
    }

    public function set(int $course_id, SetAccessAPIRequest $request)
    {
        $course = $request->getCourse();
        if ($request->has('users')) {
            if (!empty($request->input('users'))) {
                $course->users()->sync($request->input('users'));
            } else {
                $course->users()->detach();
            }
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
