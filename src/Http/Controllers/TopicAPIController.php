<?php

namespace EscolaLms\Courses\Http\Controllers;

use Error;
use EscolaLms\Courses\Exceptions\TopicException;
use EscolaLms\Courses\Http\Controllers\Swagger\TopicAPISwagger;
use EscolaLms\Courses\Http\Requests\CreateTopicAPIRequest;
use EscolaLms\Courses\Http\Requests\DeleteTopicAPIRequest;
use EscolaLms\Courses\Http\Requests\GetTopicAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateTopicAPIRequest;
use EscolaLms\Courses\Http\Resources\TopicResource;
use EscolaLms\Courses\Repositories\TopicRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class TopicController.
 */
class TopicAPIController extends AppBaseController implements TopicAPISwagger
{
    /** @var TopicRepository */
    private $topicRepository;

    public function __construct(TopicRepository $topicRepo)
    {
        $this->topicRepository = $topicRepo;
    }

    public function index(Request $request)
    {
        $topics = $this->topicRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponseForResource(TopicResource::collection($topics), 'Topics retrieved successfully');
    }

    public function store(CreateTopicAPIRequest $request)
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

        return $this->sendResponseForResource(TopicResource::make($topic), 'Topic saved successfully');
    }

    public function show($id, GetTopicAPIRequest $request)
    {
        $topic = $request->getTopic();

        if (empty($topic)) {
            return $this->sendError('Topic not found');
        }

        return $this->sendResponseForResource(TopicResource::make($topic), 'Topic retrieved successfully');
    }

    public function update($id, UpdateTopicAPIRequest $request)
    {
        if (is_null($request->getTopic())) {
            return $this->sendError('Topic not found', 404);
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

        return $this->sendResponseForResource(TopicResource::make($topic), 'Topic updated successfully');
    }

    public function destroy($id, DeleteTopicAPIRequest $request)
    {
        $topic = $request->getTopic();

        if (empty($topic)) {
            return $this->sendError('Topic not found');
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

        return $this->sendSuccess('Topic deleted successfully');
    }

    public function classes()
    {
        $classes = $this->topicRepository->availableContentClasses();

        return $this->sendResponse($classes, 'Topic content available list');
    }
}
