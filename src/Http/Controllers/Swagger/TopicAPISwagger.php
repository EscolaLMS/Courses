<?php


namespace EscolaLms\Courses\Http\Controllers\Swagger;


use EscolaLms\Courses\Http\Requests\CreateTopicAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateTopicAPIRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

interface TopicAPISwagger
{
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

    public function index(Request $request);

    /**
     * @param CreateTopicAPIRequest $request
     * @return Response
     *
     * @OA\Post(
     *      path="/api/topics",
     *      summary="Store a newly created Topic in storage",
     *      tags={"Topic"},
     *      description="Store Topic",
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

    public function store(CreateTopicAPIRequest $request);

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

    public function show($id);

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

    public function update($id, UpdateTopicAPIRequest $request);

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

    public function destroy($id);
}