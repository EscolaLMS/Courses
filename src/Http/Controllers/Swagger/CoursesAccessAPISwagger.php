<?php

namespace EscolaLms\Courses\Http\Controllers\Swagger;

use EscolaLms\Courses\Http\Requests\AddAccessAPIRequest;
use EscolaLms\Courses\Http\Requests\ListAccessAPIRequest;
use EscolaLms\Courses\Http\Requests\RemoveAccessAPIRequest;
use EscolaLms\Courses\Http\Requests\SetAccessAPIRequest;
use Illuminate\Http\JsonResponse;

interface CoursesAccessAPISwagger
{
    /**
     * @OA\Get(
     *      path="/api/admin/courses/{course_id}/access",
     *      summary="Get list of users and groups with access to the course",
     *      tags={"Admin Courses"},
     *      description="Get list of users and groups with access to the course",
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="course_id",
     *          description="id of course",
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
     *                  @OA\Schema(
     *                      type="object",
     *                      @OA\Property(
     *                          property="groups",
     *                          type="array",
     *                          @OA\Items(ref="#/components/schemas/Group")
     *                      ),
     *                      @OA\Property(
     *                          property="users",
     *                          type="array",
     *                          @OA\Items(ref="#/components/schemas/User")
     *                      ),
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function list(int $course_id, ListAccessAPIRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/admin/courses/{course_id}/access/add",
     *      summary="Add users and groups with access to the course",
     *      tags={"Admin Courses"},
     *      description="Add users and groups with access to the course",
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="course_id",
     *          description="id of course",
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
     *                  property="users",
     *                  type="array",     
     *                  @OA\Items(
     *                      type="integer",
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="groups",
     *                  type="array",
     *                  @OA\Items(
     *                      type="integer",
     *                  )
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
     *                  @OA\Schema(
     *                      type="object",
     *                      @OA\Property(
     *                          property="groups",
     *                          type="array",
     *                          @OA\Items(ref="#/components/schemas/Group")
     *                      ),
     *                      @OA\Property(
     *                          property="users",
     *                          type="array",
     *                          @OA\Items(ref="#/components/schemas/User")
     *                      ),
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function add(int $course_id, AddAccessAPIRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/admin/courses/{course_id}/access/remove",
     *      summary="Remove users and groups with access to the course",
     *      tags={"Admin Courses"},
     *      description="Remove users and groups with access to the course",
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="course_id",
     *          description="id of course",
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
     *                  property="users",
     *                  type="array",     
     *                  @OA\Items(
     *                      type="integer",
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="groups",
     *                  type="array",
     *                  @OA\Items(
     *                      type="integer",
     *                  )
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
     *                  @OA\Schema(
     *                      type="object",
     *                      @OA\Property(
     *                          property="groups",
     *                          type="array",
     *                          @OA\Items(ref="#/components/schemas/Group")
     *                      ),
     *                      @OA\Property(
     *                          property="users",
     *                          type="array",
     *                          @OA\Items(ref="#/components/schemas/User")
     *                      ),
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function remove(int $course_id, RemoveAccessAPIRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/admin/courses/{course_id}/access/set",
     *      summary="Set users and groups with access to the course",
     *      tags={"Admin Courses"},
     *      description="Set users and groups with access to the course",
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="course_id",
     *          description="id of course",
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
     *                  property="users",
     *                  type="array",     
     *                  @OA\Items(
     *                      type="integer",
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="groups",
     *                  type="array",
     *                  @OA\Items(
     *                      type="integer",
     *                  )
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
     *                  @OA\Schema(
     *                      type="object",
     *                      @OA\Property(
     *                          property="groups",
     *                          type="array",
     *                          @OA\Items(ref="#/components/schemas/Group")
     *                      ),
     *                      @OA\Property(
     *                          property="users",
     *                          type="array",
     *                          @OA\Items(ref="#/components/schemas/User")
     *                      ),
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function set(int $course_id, SetAccessAPIRequest $request): JsonResponse;
}
