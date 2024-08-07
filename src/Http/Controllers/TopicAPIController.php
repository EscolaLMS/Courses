<?php

namespace EscolaLms\Courses\Http\Controllers;

use Error;
use EscolaLms\Courses\Exceptions\TopicException;
use EscolaLms\Courses\Http\Controllers\Swagger\TopicAPISwagger;
use EscolaLms\Courses\Http\Requests\CloneTopicAPIRequest;
use EscolaLms\Courses\Http\Requests\CreateTopicAPIRequest;
use EscolaLms\Courses\Http\Requests\DeleteTopicAPIRequest;
use EscolaLms\Courses\Http\Requests\GetTopicAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateTopicAPIRequest;
use EscolaLms\Courses\Http\Resources\Admin\TopicAdminResource;
use EscolaLms\Courses\Http\Resources\TopicResource;
use EscolaLms\Courses\Repositories\Contracts\TopicRepositoryContract;
use EscolaLms\Courses\Services\Contracts\TopicServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class TopicController.
 */
class TopicAPIController extends AppBaseController implements TopicAPISwagger
{
    private TopicRepositoryContract $topicRepository;

    private TopicServiceContract $topicService;

    public function __construct(TopicRepositoryContract $topicRepo, TopicServiceContract $topicService)
    {
        $this->topicRepository = $topicRepo;
        $this->topicService = $topicService;
    }

    public function index(Request $request)
    {
        $topics = $this->topicRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        )->load('topicable', 'resources', 'topicable.topic');

        return $this->sendResponseForResource(TopicResource::collection($topics), __('Topics retrieved successfully'));
    }

    public function store(CreateTopicAPIRequest $request): JsonResponse
    {
        try {
            $topic = $this->topicRepository->createFromRequest($request);
        } catch (AccessDeniedHttpException $error) {
            return $this->sendError($error->getMessage(), 403);
        } catch (TopicException $error) {
            return $this->sendDataError($error->getMessage(), $error->getData());
        } catch (Error $error) {
            return $this->sendError($error->getMessage(), 422);
        }

        return $this->sendResponseForResource(TopicAdminResource::make($topic), __('Topic saved successfully'));
    }

    public function show($id, GetTopicAPIRequest $request)
    {
        $topic = $request->getTopic();

        if (empty($topic)) {
            return $this->sendError(__('Topic not found'));
        }

        return $this->sendResponseForResource(TopicResource::make($topic), __('Topic retrieved successfully'));
    }

    public function update($id, UpdateTopicAPIRequest $request)
    {
        if (is_null($request->getTopic())) {
            return $this->sendError(__('Topic not found'), 404);
        }

        try {
            $topic = $this->topicRepository->updateFromRequest($request);
        } catch (AccessDeniedHttpException $error) {
            return $this->sendError($error->getMessage(), 403);
        } catch (TopicException $error) {
            return $this->sendDataError($error->getMessage(), $error->getData());
        } catch (Error $error) {
            return $this->sendError($error->getMessage(), 422);
        }

        return $this->sendResponseForResource(TopicAdminResource::make($topic), __('Topic updated successfully'));
    }

    public function destroy($id, DeleteTopicAPIRequest $request)
    {
        $topic = $request->getTopic();

        if (empty($topic)) {
            return $this->sendError(__('Topic not found'));
        }

        try {
            $this->topicRepository->delete($id);
        } catch (AccessDeniedHttpException $error) {
            return $this->sendError($error->getMessage(), 403);
        } catch (TopicException $error) {
            return $this->sendDataError($error->getMessage(), $error->getData());
        } catch (Error $error) {
            return $this->sendError($error->getMessage(), 422);
        }

        return $this->sendSuccess(__('Topic deleted successfully'));
    }

    public function classes(): JsonResponse
    {
        $classes = $this->topicRepository->availableContentClasses();

        return $this->sendResponse($classes, __('Topic content available list'));
    }

    public function clone(CloneTopicAPIRequest $request): JsonResponse
    {
        $topic = $request->getTopic();

        if (empty($topic)) {
            return $this->sendError(__('Topic not found'));
        }

        try {
            $topic = $this->topicService->cloneTopic($topic);
        } catch (\Exception $error) {
            return $this->sendError('Error', 400);
        }

        return $this->sendResponseForResource(TopicAdminResource::make($topic), __('Topic cloned successfully'));
    }
}
