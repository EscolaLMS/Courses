<?php

namespace EscolaLms\Courses\Http\Controllers\Swagger;

use EscolaLms\Courses\Http\Requests\CreateLessonAPIRequest;
use EscolaLms\Courses\Http\Requests\DeleteLessonAPIRequest;
use EscolaLms\Courses\Http\Requests\GetLessonAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateLessonAPIRequest;
use Illuminate\Http\Request;

interface LessonAPISwagger
{
    /**
     * @OA\Get(
     *      path="/api/admin/lessons",
     *      summary="Get a listing of the Lessons.",
     *      tags={"Admin Courses"},
     *      description="Get all Lessons",
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
     *                  @OA\Items(ref="#/components/schemas/Lesson")
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
     *      path="/api/admin/lessons",
     *      summary="Store a newly created Lesson in storage",
     *      tags={"Admin Courses"},
     *      description="Store Lesson",
     *     security={
     *         {"passport": {}},
     *     },
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/Lesson")
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
     *                  ref="#/components/schemas/Lesson"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */

    public function store(CreateLessonAPIRequest $request);

    /**
     * @OA\Get(
     *      path="/api/admin/lessons/{id}",
     *      summary="Display the specified Lesson",
     *      tags={"Admin Courses"},
     *      description="Get Lesson",
     *     security={
     *         {"passport": {}},
     *     },
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Lesson",
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
     *                  ref="#/components/schemas/Lesson"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */

    public function show($id, GetLessonAPIRequest $request);

    /**
     * @OA\Put(
     *      path="/api/admin/lessons/{id}",
     *      summary="Update the specified Lesson in storage",
     *      tags={"Admin Courses"},
     *      description="Update Lesson",
     *     security={
     *         {"passport": {}},
     *     },
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Lesson",
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
     *              @OA\Schema(ref="#/components/schemas/Lesson")
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
     *                  ref="#/components/schemas/Lesson"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */

    public function update($id, UpdateLessonAPIRequest $request);

    /**
     * @OA\Delete(
     *      path="/api/admin/lessons/{id}",
     *      summary="Remove the specified Lesson from storage",
     *      tags={"Admin Courses"},
     *      description="Delete Lesson",
     *     security={
     *         {"passport": {}},
     *     },
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Lesson",
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

    public function destroy($id, DeleteLessonAPIRequest $request);
}
