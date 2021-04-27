<?php

namespace EscolaLms\Courses\Http\Controllers;

use App\Http\Requests\CreateTopicRichTextAPIRequest;
use App\Http\Requests\UpdateTopicRichTextAPIRequest;
use EscolaLms\Courses\Models\TopicRichText;
use EscolaLms\Courses\Repositories\TopicRichTextRepository;
use Illuminate\Http\Request;
use EscolaLms\Courses\Http\Controllers\AppBaseController;
use Response;

/**
 * Class TopicRichTextController
 * @package App\Http\Controllers
 */

class TopicRichTextAPIController extends AppBaseController
{
    /** @var  TopicRichTextRepository */
    private $topicRichTextRepository;

    public function __construct(TopicRichTextRepository $topicRichTextRepo)
    {
        $this->topicRichTextRepository = $topicRichTextRepo;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @OA\Get(
     *      path="/api/topicRichTexts",
     *      summary="Get a listing of the TopicRichTexts.",
     *      tags={"TopicRichText"},
     *      description="Get all TopicRichTexts",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
    *          @OA\MediaType(
    *              mediaType="application/json"
    *          ),
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/TopicRichText")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */

    public function index(Request $request)
    {
        $topicRichTexts = $this->topicRichTextRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($topicRichTexts->toArray(), 'Topic Rich Texts retrieved successfully');
    }

    /**
     * @param CreateTopicRichTextAPIRequest $request
     * @return Response
     *
     * @OA\Post(
     *      path="/api/topicRichTexts",
     *      summary="Store a newly created TopicRichText in storage",
     *      tags={"TopicRichText"},
     *      description="Store TopicRichText",
    *      @OA\RequestBody(
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(ref="#/components/schemas/TopicRichText")
    *          )
    *      ),

     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
    *          @OA\MediaType(
    *              mediaType="application/json"
    *          ),
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/TopicRichText"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */

    public function store(CreateTopicRichTextAPIRequest $request)
    {
        $input = $request->all();

        $topicRichText = $this->topicRichTextRepository->create($input);

        return $this->sendResponse($topicRichText->toArray(), 'Topic Rich Text saved successfully');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @OA\Get(
     *      path="/api/topicRichTexts/{id}",
     *      summary="Display the specified TopicRichText",
     *      tags={"TopicRichText"},
     *      description="Get TopicRichText",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of TopicRichText",
    *          @OA\Schema(
    *             type="integer",
    *         ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
    *          @OA\MediaType(
    *              mediaType="application/json"
    *          ),
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/TopicRichText"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */

    public function show($id)
    {
        /** @var TopicRichText $topicRichText */
        $topicRichText = $this->topicRichTextRepository->find($id);

        if (empty($topicRichText)) {
            return $this->sendError('Topic Rich Text not found');
        }

        return $this->sendResponse($topicRichText->toArray(), 'Topic Rich Text retrieved successfully');
    }

    /**
     * @param int $id
     * @param UpdateTopicRichTextAPIRequest $request
     * @return Response
     *
     * @OA\Put(
     *      path="/api/topicRichTexts/{id}",
     *      summary="Update the specified TopicRichText in storage",
     *      tags={"TopicRichText"},
     *      description="Update TopicRichText",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of TopicRichText",
    *          @OA\Schema(
    *             type="integer",
    *         ),
     *          required=true,
     *          in="path"
     *      ),
    *      @OA\RequestBody(
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(ref="#/components/schemas/TopicRichText")
    *          )
    *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
    *          @OA\MediaType(
    *              mediaType="application/json"
    *          ),
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/TopicRichText"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */

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

    /**
     * @param int $id
     * @return Response
     *
     * @OA\Delete(
     *      path="/api/topicRichTexts/{id}",
     *      summary="Remove the specified TopicRichText from storage",
     *      tags={"TopicRichText"},
     *      description="Delete TopicRichText",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of TopicRichText",
    *          @OA\Schema(
    *             type="integer",
    *         ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
    *          @OA\MediaType(
    *              mediaType="application/json"
    *          ),
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */

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
