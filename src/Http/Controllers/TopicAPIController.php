<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Courses\Http\Controllers\Swagger\TopicAPISwagger;
use EscolaLms\Courses\Http\Requests\CreateTopicAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateTopicAPIRequest;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Repositories\TopicRepository;
use Illuminate\Http\Request;
use Response;
use EscolaLms\Courses\Exceptions\TopicException;
use Error;

/**
 * Class TopicController
 * @package App\Http\Controllers
 */

class TopicAPIController extends AppBaseController implements TopicAPISwagger
{
    /** @var  TopicRepository */
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

        return $this->sendResponse($topics->toArray(), 'Topics retrieved successfully');
    }



    public function store(CreateTopicAPIRequest $request)
    {
        $input = $request->all();

        try {
            $topic = $this->topicRepository->create($input);
        } catch (TopicException $error) {
            return $this->sendDataError($error->getMessage(), $error->getData());
        } catch (Error $error) {
            return $this->sendError($error->getMessage(), 422);
        }

        return $this->sendResponse($topic->toArray(), 'Topic saved successfully');
    }

    public function show($id)
    {
        /** @var Topic $topic */
        $topic = $this->topicRepository->find($id);

        if (empty($topic)) {
            return $this->sendError('Topic not found');
        }

        return $this->sendResponse($topic->toArray(), 'Topic retrieved successfully');
    }

    public function update($id, UpdateTopicAPIRequest $request)
    {
        $input = $request->all();

        /** @var Topic $topic */
        $topic = $this->topicRepository->find($id);

        if (empty($topic)) {
            return $this->sendError('Topic not found');
        }

        $topic = $this->topicRepository->update($input, $id);

        return $this->sendResponse($topic->toArray(), 'Topic updated successfully');
    }

    public function destroy($id)
    {
        /** @var Topic $topic */
        $topic = $this->topicRepository->find($id);

        if (empty($topic)) {
            return $this->sendError('Topic not found');
        }

        $topic->delete();

        return $this->sendSuccess('Topic deleted successfully');
    }


    public function classes()
    {
        $classes = $this->topicRepository->availableContentClasses();

        return $this->sendResponse($classes, 'Topic content availabe list');
    }
}
