<?php

namespace EscolaLms\Courses\Http\Controllers\Swagger;

use EscolaLms\Courses\Http\Requests\CourseProgressAPIRequest;
use EscolaLms\Courses\Http\Requests\CourseProgressPaginatedListRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface CourseProgressAPISwagger
{
    /**
     * @OA\Get(
     *      tags={"Courses"},
     *      path="/api/courses/progress",
     *      description="Get Progresses",
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Bad request",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      )
     *   )
     */
    public function index(Request $request): JsonResponse;

    /**
     * @OA\Get(
     *      tags={"Courses"},
     *      path="/api/courses/progress/paginated",
     *      description="Get Paginated Progresses",
     *      security={
     *          {"passport": {}},
     *      },
     *     @OA\Parameter(
     *          name="order_by",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *     @OA\Parameter(
     *          name="order",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="string",
     *              enum={"title", "obtained"}
     *          ),
     *      ),
     *     @OA\Parameter(
     *           name="status",
     *           required=false,
     *           in="path",
     *           @OA\Schema(
     *               type="string",
     *               enum={"planned", "started", "finished"}
     *           ),
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Bad request",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      )
     *   )
     */
    public function indexPaginated(CourseProgressPaginatedListRequest $request): JsonResponse;

    /**
     * @OA\Get(
     *      tags={"Courses"},
     *      path="/api/courses/progress/{course_id}",
     *      description="Show user course progress",
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="course_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="number",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Bad request",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      )
     *   )
     */
    public function show($course_id, Request $request): JsonResponse;

    /**
     * @OA\Patch(
     *      tags={"Courses"},
     *      path="/api/courses/progress/{course_id}",
     *      description="Show user course progress",
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="course_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="number",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          required=true,
     *          in="query",
     *          name="progress[]",
     *          @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="topic_id",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      type="integer"
     *                  ),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Bad request",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      )
     *   )
     */
    public function store($course_id, CourseProgressAPIRequest $request): JsonResponse;

    /**
     * @OA\Put(
     *      tags={"Courses"},
     *      path="/api/courses/progress/{topic_id}/ping",
     *      description="Update time in course by ping.",
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="topic_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="number",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Bad request",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      )
     *   )
     */
    public function ping($topic_id, Request $request): JsonResponse;

    /**
     * @OA\Post(
     *      tags={"Courses"},
     *      path="/api/courses/progress/{topic_id}/h5p",
     *      description="Update h5p progress in course quiz.",
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="topic_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="number",
     *          ),
     *      ),
     *      @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="event",
     *                 type="string",
     *                 example="http://adlnet.gov/expapi/verbs/attempted",
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *             ),
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Bad request",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      )
     *   )
     */
    public function h5p($topic_id, Request $request): JsonResponse;
}
