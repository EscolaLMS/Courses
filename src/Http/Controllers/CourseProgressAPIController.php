<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Core\Http\Resources\Status;
use EscolaLms\Courses\Enum\CourseStatusEnum;
use EscolaLms\Courses\Http\Controllers\Swagger\CourseProgressAPISwagger;
use EscolaLms\Courses\Http\Requests\CourseProgressAPIRequest;
use EscolaLms\Courses\Http\Resources\ProgressResource;
use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use EscolaLms\Courses\Repositories\Contracts\TopicRepositoryContract;
use EscolaLms\Courses\Services\Contracts\ProgressServiceContract;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseProgressAPIController extends AppBaseController implements CourseProgressAPISwagger
{
    protected ProgressServiceContract $progressServiceContract;
    protected TopicRepositoryContract $topicRepositoryContract;
    protected CourseRepositoryContract $courseRepositoryContract;

    public function __construct(
        ProgressServiceContract $progressServiceContract,
        TopicRepositoryContract $topicRepositoryContract,
        CourseRepositoryContract $courseRepositoryContract
    ) {
        $this->progressServiceContract = $progressServiceContract;
        $this->topicRepositoryContract = $topicRepositoryContract;
        $this->courseRepositoryContract = $courseRepositoryContract;
    }

    public function index(Request $request): JsonResponse
    {
        return $this->sendResponseForResource(ProgressResource::collection($this->progressServiceContract->getByUser($request->user())), __('Progresses'));
    }

    /**
     * Display the specified CourseProgress.
     */
    public function show($course_id, Request $request): JsonResponse
    {
        $course = $this->courseRepositoryContract->getById($course_id);

        if ($course->status !== CourseStatusEnum::PUBLISHED) {
            // We only check $course->active, and not is_active, because if course has deadline we still want to return progress
            return $this->sendError(__('Course is not active'), 403);
        }

        return $this->sendResponse(CourseProgressCollection::make($request->user(), $course)->getProgress(), __('Progress'));
    }

    /**
     * Update the specified CourseProgress in storage.
     */
    public function store($course_id, CourseProgressAPIRequest $request): JsonResponse
    {
        $course = $this->courseRepositoryContract->getById($course_id);

        if (!$course->active) {
            return $this->sendError(__('Course is not active'), 403);
        }

        $courseProgressCollection = $this->progressServiceContract->update($course, $request->user(), $request->get('progress'));

        if ($courseProgressCollection->afterDeadline()) {
            return $this->sendError(__('Deadline missed'), 403);
        }

        return $this->sendResponse($courseProgressCollection->getProgress(), __('Saved progress'));
    }

    public function ping($topic_id, Request $request): JsonResponse
    {
        $topic = $this->topicRepositoryContract->getById($topic_id);

        if ($topic->course !== CourseStatusEnum::PUBLISHED) {
            return $this->sendError(__('Course is not active'), 403);
        }
        if (!$topic->active) {
            return $this->sendError(__('Topic is not active'), 403);
        }

        $courseProgressCollection = $this->progressServiceContract->ping($request->user(), $topic);

        if ($courseProgressCollection->afterDeadline()) {
            return $this->sendError(__('Deadline missed'), 403);
        }

        return $this->sendResponseForResource(new Status(true), 'Status');
    }

    /**
     * Saves CourseH5PProgress in storage.
     */
    public function h5p($topic_id, Request $request): JsonResponse
    {
        $topic = $this->topicRepositoryContract->getById($topic_id);

        if (!$topic->course->is_active) {
            return $this->sendError(__('Course is not active'), 403);
        }
        if (!$topic->is_active) {
            return $this->sendError(__('Topic is not active'), 403);
        }

        $result = $this->progressServiceContract->h5p(
            $request->user(),
            $topic,
            $request->input('event'),
            $request->input('data'),
        );

        if ($result) {
            return $this->sendResponseForResource(new Status(true), 'Status');
        }
        return $this->sendError(__('Deadline missed'), 403);

    }
}
