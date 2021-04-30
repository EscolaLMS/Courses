<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Courses\Http\Controllers\Swagger\TopicRichTextAPISwagger;
use EscolaLms\Courses\Http\Requests\CreateTopicRichTextAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateTopicRichTextAPIRequest;
use EscolaLms\Courses\Models\TopicRichText;
use EscolaLms\Courses\Repositories\TopicRichTextRepository;
use Illuminate\Http\Request;
use Response;

/**
 * Class TopicRichTextController
 * @package App\Http\Controllers
 */

class TopicRichTextAPIController extends AppBaseController implements TopicRichTextAPISwagger
{
    /** @var  TopicRichTextRepository */
    private $topicRichTextRepository;

    public function __construct(TopicRichTextRepository $topicRichTextRepo)
    {
        $this->topicRichTextRepository = $topicRichTextRepo;
    }

    public function index(Request $request)
    {
        $topicRichTexts = $this->topicRichTextRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($topicRichTexts->toArray(), 'Topic Rich Texts retrieved successfully');
    }

    public function store(CreateTopicRichTextAPIRequest $request)
    {
        $input = $request->all();

        $topicRichText = $this->topicRichTextRepository->create($input);

        return $this->sendResponse($topicRichText->toArray(), 'Topic Rich Text saved successfully');
    }

    public function show($id)
    {
        /** @var TopicRichText $topicRichText */
        $topicRichText = $this->topicRichTextRepository->find($id);

        if (empty($topicRichText)) {
            return $this->sendError('Topic Rich Text not found');
        }

        return $this->sendResponse($topicRichText->toArray(), 'Topic Rich Text retrieved successfully');
    }

    public function update($id, UpdateTopicRichTextAPIRequest $request)
    {
        $input = $request->all();

        /** @var TopicRichText $topicRichText */
        $topicRichText = $this->topicRichTextRepository->find($id);

        if (empty($topicRichText)) {
            return $this->sendError('Topic Rich Text not found');
        }

        $topicRichText = $this->topicRichTextRepository->update($input, $id);

        return $this->sendResponse($topicRichText->toArray(), 'TopicRichText updated successfully');
    }

    public function destroy($id)
    {
        /** @var TopicRichText $topicRichText */
        $topicRichText = $this->topicRichTextRepository->find($id);

        if (empty($topicRichText)) {
            return $this->sendError('Topic Rich Text not found');
        }

        $topicRichText->delete();

        return $this->sendSuccess('Topic Rich Text deleted successfully');
    }
}
