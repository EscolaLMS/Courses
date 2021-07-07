<?php

namespace EscolaLms\Courses\Http\Controllers\Swagger;

use EscolaLms\Courses\Http\Requests\CreateTopicAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateTopicAPIRequest;
use EscolaLms\Courses\Http\Requests\DeleteTopicAPIRequest;
use EscolaLms\Courses\Http\Requests\GetTopicAPIRequest;

use Illuminate\Http\Request;

interface TopicAPISwagger
{
    /**
     * @OA\Get(
     *      path="/api/admin/topics",
     *      summary="Get a listing of the Topics.",
     *      tags={"Course"},
     *      description="Get all Topics",
     *     security={
     *         {"passport": {}},
     *     },
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

    public function index(Request $request);

    /**
     * @param CreateTopicAPIRequest $request
     * @return Response
     *
     * @OA\Post(
     *      path="/api/admin/topics",
     *      summary="Store a newly created Topic in storage",
     *      tags={"Course"},
     *     security={
     *         {"passport": {}},
     *     },
     *      description="Store Topic. Depending on `topicable_type` values are different. Endpoint does create both `Topic` and 1:1 related `Content` based on creating class ",
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

    public function store(CreateTopicAPIRequest $request);

    /**
     * @OA\Get(
     *      path="/api/admin/topics/{id}",
     *      summary="Display the specified Topic",
     *      tags={"Course"},
     *      description="Get Topic",
     *     security={
     *         {"passport": {}},
     *     },
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

    public function show($id, GetTopicAPIRequest $request);

    /**
     * @OA\Put(
     *      path="/api/admin/topics/{id}",
     *      summary="Update the specified Topic in storage",
     *      tags={"Course"},
     *      description="Update Topic",
     *     security={
     *         {"passport": {}},
     *     },
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

    public function update($id, UpdateTopicAPIRequest $request);

    /**
     * @OA\Delete(
     *      path="/api/admin/topics/{id}",
     *      summary="Remove the specified Topic from storage",
     *      tags={"Course"},
     *      description="Delete Topic",
     *     security={
     *         {"passport": {}},
     *     },
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

    public function destroy($id, DeleteTopicAPIRequest $request);

    /**
     * @param Request $request
     * @return Response
     *
     * @OA\Get(
     *      path="/api/admin/topics/types",
     *      summary="Get a listing of the Availabe Topic Content Types Classes.",
     *      tags={"Course"},
     *      description="Get all Topic Contents",
     *     security={
     *         {"passport": {}},
     *     },
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

    public function classes();
}
