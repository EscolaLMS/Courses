<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Core\Http\Resources\Status;
use EscolaLms\Courses\Http\Controllers\Swagger\CourseProgressAPISwagger;
use EscolaLms\Courses\Http\Requests\CourseProgressAPIRequest;
use EscolaLms\Courses\Http\Resources\ProgressesResource;
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
        try {
            return (new ProgressesResource($this->progressServiceContract->getByUser($request->user())))->response();
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    /**
     * Display the specified CourseProgress.
     */
    public function show($course_id, Request $request): JsonResponse
    {
        try {
            $course = $this->courseRepositoryContract->getById($course_id);
            return new JsonResponse(CourseProgressCollection::make($request->user(), $course)->getProgress());
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    /**
     * Update the specified CourseProgress in storage.
     */
    public function store($course_id, CourseProgressAPIRequest $request): JsonResponse
    {
        try {
            $course = $this->courseRepositoryContract->getById($course_id);
            $progress = $this->progressServiceContract->update($course, $request->user(), $request->get('progress'));
            return new JsonResponse($progress->getProgress());
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    public function ping($topic_id, Request $request): JsonResponse
    {
        try {
            $topic = $this->topicRepositoryContract->getById($topic_id);
            $this->progressServiceContract->ping($request->user(), $topic);
            return (new Status(true))->response();
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    /**
     * Saves CourseH5PProgress in storage.
     */
    public function h5p($topic_id, Request $request): JsonResponse
    {
        try {
            $topic = $this->topicRepositoryContract->getById($topic_id);
            $this->progressServiceContract->h5p(
                $request->user(),
                $topic,
                $request->input('event'),
                $request->input('data'),
            );

            return (new Status(true))->response();
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }
}
