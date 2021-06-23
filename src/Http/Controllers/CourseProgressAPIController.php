<?php


namespace EscolaLms\Courses\Http\Controllers;


use EscolaLms\Core\Http\Resources\Status;
use EscolaLms\Courses\Http\Controllers\Swagger\CourseProgressAPISwagger;
use EscolaLms\Courses\Http\Requests\CourseProgressAPIRequest;
use EscolaLms\Courses\Http\Resources\ProgressesResource;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Repositories\Contracts\CourseProgressRepositoryContract;
use EscolaLms\Courses\Repositories\Contracts\TopicRepositoryContract;
use EscolaLms\Courses\Services\Contracts\ProgressServiceContract;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseProgressAPIController extends AppBaseController implements CourseProgressAPISwagger
{
    protected ProgressServiceContract $progressServiceContract;
    protected TopicRepositoryContract $topicRepositoryContract;

    public function __construct(
        ProgressServiceContract $progressServiceContract,
        TopicRepositoryContract $topicRepositoryContract
    )
    {
        $this->progressServiceContract = $progressServiceContract;
        $this->topicRepositoryContract = $topicRepositoryContract;
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
    public function show(Course $course, Request $request): JsonResponse
    {
        try {
            return new JsonResponse(CourseProgressCollection::make($request->user(), $course)->getProgress());
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
}