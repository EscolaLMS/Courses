<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Courses\Http\Requests\CreateTopicAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateTopicAPIRequest;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Repositories\TopicRepository;
use Illuminate\Http\Request;
use EscolaLms\Courses\Http\Controllers\AppBaseController;
use Response;
use EscolaLms\Courses\Exceptions\TopicException;
use Error;

/**
 * Class TopicController
 * @package App\Http\Controllers
 */

class TopicAPIController extends AppBaseController
{
    /** @var  TopicRepository */
    private $topicRepository;

    public function __construct(TopicRepository $topicRepo)
    {
        $this->topicRepository = $topicRepo;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @OA\Get(
     *      path="/api/topics",
     *      summary="Get a listing of the Topics.",
     *      tags={"Topic"},
     *      description="Get all Topics",
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
     *                  @OA\Items(ref="#/components/schemas/Topic")
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
        $topics = $this->topicRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($topics->toArray(), 'Topics retrieved successfully');
    }

    /**
     * @param CreateTopicAPIRequest $request
     * @return Response
     *
     * @OA\Post(
     *      path="/api/topics",
     *      summary="Store a newly created Topic in storage",
     *      tags={"Topic"},
     *      description="Store Topic. Depending on `topicable_class` values are different. Endpoint does create both `Topic` and 1:1 related `Content` based on creating class ",
    *      @OA\RequestBody(
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/x-www-form-urlencoded",
    *              @OA\Schema(ref="#/components/schemas/Topic")
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
     *                  ref="#/components/schemas/Topic"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */

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

    /**
     * @param int $id
     * @return Response
     *
     * @OA\Get(
     *      path="/api/topics/{id}",
     *      summary="Display the specified Topic",
     *      tags={"Topic"},
     *      description="Get Topic",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Topic",
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
     *                  ref="#/components/schemas/Topic"
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
        /** @var Topic $topic */
        $topic = $this->topicRepository->find($id);

        if (empty($topic)) {
            return $this->sendError('Topic not found');
        }

        return $this->sendResponse($topic->toArray(), 'Topic retrieved successfully');
    }

    /**
     * @param int $id
     * @param UpdateTopicAPIRequest $request
     * @return Response
     *
     * @OA\Put(
     *      path="/api/topics/{id}",
     *      summary="Update the specified Topic in storage",
     *      tags={"Topic"},
     *      description="Update Topic",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Topic",
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
    *              @OA\Schema(ref="#/components/schemas/Topic")
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
     *                  ref="#/components/schemas/Topic"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */

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

    /**
     * @param int $id
     * @return Response
     *
     * @OA\Delete(
     *      path="/api/topics/{id}",
     *      summary="Remove the specified Topic from storage",
     *      tags={"Topic"},
     *      description="Delete Topic",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Topic",
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
        /** @var Topic $topic */
        $topic = $this->topicRepository->find($id);

        if (empty($topic)) {
            return $this->sendError('Topic not found');
        }

        $topic->delete();

        return $this->sendSuccess('Topic deleted successfully');
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @OA\Get(
     *      path="/api/topics/types",
     *      summary="Get a listing of the Availabe Topic Content Types Classes.",
     *      tags={"Topic"},
     *      description="Get all Topic Contents",
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
     *                  @OA\Items(type="string")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function classes()
    {
        $classes = $this->topicRepository->availableContentClasses();

        return $this->sendResponse($classes, 'Topic content availabe list');
    }
}
