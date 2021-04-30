<?php

namespace EscolaLms\Courses\Http\Controllers\Swagger;

use EscolaLms\Courses\Http\Requests\CreateTopicRichTextAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateTopicRichTextAPIRequest;
use Illuminate\Http\Request;

interface TopicRichTextAPISwagger
{
    /**
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

    public function index(Request $request);

    /**
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

    public function store(CreateTopicRichTextAPIRequest $request);

    /**
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

    public function show($id);

    /**
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

    public function update($id, UpdateTopicRichTextAPIRequest $request);

    /**
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

    public function destroy($id);
}