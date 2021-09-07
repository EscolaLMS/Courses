<?php

namespace EscolaLms\Courses\Http\Controllers\Swagger;

use EscolaLms\Courses\Http\Requests\DeleteTopicResourceAPIRequest;
use EscolaLms\Courses\Http\Requests\ListTopicResourceAPIRequest;
use EscolaLms\Courses\Http\Requests\RenameTopicResourceAPIRequest;
use EscolaLms\Courses\Http\Requests\UploadTopicResourceAPIRequest;
use Illuminate\Http\JsonResponse;

interface TopicResourcesAPISwagger
{
    /**
     * @OA\Get(
     *      path="/topics/{topic_id}/resources/",
     *      summary="Get list of resources",
     *      tags={"Admin Courses"},
     *      description="Get list of resources",
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="topic_id",
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
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/TopicResource")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function list(ListTopicResourceAPIRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/topics/{topic_id}/resources/",
     *      summary="Post new resource",
     *      tags={"Admin Courses"},
     *      description="Post new resource",
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="topic_id",
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
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="resource",
     *                      type="string",
     *                      format="binary"
     *                  )
     *              )
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
     *                  ref="#/components/schemas/TopicResource"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function upload(UploadTopicResourceAPIRequest $request): JsonResponse;

    /**
     * @OA\Delete(
     *      path="/topics/{topic_id}/resources/{resource_id}",
     *      summary="Delete resource",
     *      tags={"Admin Courses"},
     *      description="Delete resource,
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="topic_id",
     *          description="id of Topic",
     *          @OA\Schema(
     *             type="integer",
     *         ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\Parameter(
     *          name="resource_id",
     *          description="id of Resource",
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
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function delete(DeleteTopicResourceAPIRequest $request): JsonResponse;


    /**
     * @OA\Patch(
     *      path="/topics/{topic_id}/resources/{resource_id}",
     *      summary="Rename resource",
     *      tags={"Admin Courses"},
     *      description="Rename resource,
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="topic_id",
     *          description="id of Topic",
     *          @OA\Schema(
     *             type="integer",
     *         ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\Parameter(
     *          name="resource_id",
     *          description="id of Resource",
     *          @OA\Schema(
     *             type="integer",
     *         ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="name",
     *                  type="string",
     *              ),
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
     *                  ref="#/components/schemas/TopicResource"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function rename(RenameTopicResourceAPIRequest $request): JsonResponse;
}
