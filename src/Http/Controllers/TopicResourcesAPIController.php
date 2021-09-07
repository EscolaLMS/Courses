<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Courses\Http\Controllers\Swagger\TopicResourcesAPISwagger;
use EscolaLms\Courses\Http\Requests\DeleteTopicResourceAPIRequest;
use EscolaLms\Courses\Http\Requests\ListTopicResourceAPIRequest;
use EscolaLms\Courses\Http\Requests\RenameTopicResourceAPIRequest;
use EscolaLms\Courses\Http\Requests\UploadTopicResourceAPIRequest;
use EscolaLms\Courses\Http\Resources\TopicResourceResource;
use EscolaLms\Courses\Repositories\Contracts\TopicRepositoryContract;
use EscolaLms\Courses\Repositories\Contracts\TopicResourceRepositoryContract;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class TopicResourcesAPIController extends AppBaseController implements TopicResourcesAPISwagger
{
    protected TopicRepositoryContract $topicRepository;
    protected TopicResourceRepositoryContract $resourceRepository;

    public function __construct(TopicRepositoryContract $topicRepository, TopicResourceRepositoryContract $resourceRepository)
    {
        $this->topicRepository = $topicRepository;
        $this->resourceRepository = $resourceRepository;
    }

    public function list(ListTopicResourceAPIRequest $request): JsonResponse
    {
        return $this->sendResponseForResource(TopicResourceResource::collection($request->getTopic()->resources), 'Topic resources retrieved successfully');
    }

    public function upload(UploadTopicResourceAPIRequest $request): JsonResponse
    {
        try {
            $topicResource = $this->resourceRepository->storeUploadedResourceForTopic($request->getTopic(), $request->getUploadedResource());
            return $this->sendResponseForResource(TopicResourceResource::make($topicResource), 'Topic resource uploaded successfully');
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), 422);
        }
    }

    public function delete(DeleteTopicResourceAPIRequest $request): JsonResponse
    {
        try {
            $deleted = $this->resourceRepository->delete($request->getTopicResourceId());
            if ($deleted) {
                return $this->sendSuccess(__('Deleted topic resource'));
            }
            return $this->sendError(__('Failed to delete topic resource'));
        } catch (ModelNotFoundException $exception) {
            return $this->sendError($exception->getMessage());
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), 422);
        }
    }

    public function rename(RenameTopicResourceAPIRequest $request): JsonResponse
    {
        $topicResource = $this->resourceRepository->find($request->getTopicResourceId());
        if (empty($topicResource)) {
            return $this->sendError(__('Topic resource not found'));
        }
        try {
            if ($this->resourceRepository->renameModel($topicResource, $request->getName())) {
                return $this->sendResponseForResource(TopicResourceResource::make($topicResource->refresh()), 'Topic resource renamed successfully');
            }
        } catch (ModelNotFoundException $exception) {
            return $this->sendError($exception->getMessage());
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), 422);
        }
    }
}
