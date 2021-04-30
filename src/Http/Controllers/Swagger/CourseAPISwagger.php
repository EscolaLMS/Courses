<?php

namespace EscolaLms\Courses\Http\Controllers\Swagger;

use EscolaLms\Courses\Http\Requests\AttachCategoriesCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\AttachTagsCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\CreateCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateCourseAPIRequest;
use Illuminate\Http\Request;

interface CourseAPISwagger
{
    /**
     * @OA\Get(
     *      path="/api/courses",
     *      summary="Get a listing of the Courses.",
     *      tags={"Course"},
     *      description="Get all Courses",
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
     *                  @OA\Items(ref="#/components/schemas/Course")
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
     *      path="/api/courses",
     *      summary="Store a newly created Course in storage",
     *      tags={"Course"},
     *      description="Store Course",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/Course")
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
     *                  ref="#/components/schemas/Course"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */

    public function store(CreateCourseAPIRequest $request);

    /**
     * @OA\Get(
     *      path="/api/courses/{id}",
     *      summary="Display the specified Course",
     *      tags={"Course"},
     *      description="Get Course",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Course",
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
     *                  ref="#/components/schemas/Course"
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
     *      path="/api/courses/{id}",
     *      summary="Update the specified Course in storage",
     *      tags={"Course"},
     *      description="Update Course",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Course",
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
     *              @OA\Schema(ref="#/components/schemas/Course")
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
     *                  ref="#/components/schemas/Course"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */

    public function update($id, UpdateCourseAPIRequest $request);

    /**
     * @OA\Delete(
     *      path="/api/courses/{id}",
     *      summary="Remove the specified Course from storage",
     *      tags={"Course"},
     *      description="Delete Course",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Course",
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

    /**
     * @OA\Get(
     *      tags={"Courses"},
     *      path="/api/courses/category/{category_id}",
     *      description="Searche Course By Criteria",
     *      operationId="searchCourseByCategory",
     *      @OA\Parameter(
     *          name="category_id",
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

    public function category(int $category_id, Request $request);

    /**
     * @OA\Post(
     *      path="/api/courses/attach/{id}/categories",
     *      summary="Attach categories for couse",
     *      tags={"Course"},
     *      description="Attach categories for couse",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="categories",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         type="number",
     *                         example="1"
     *                      ),
     *                  ),
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          ),
     *      )
     * )
     */

    public function attachCategory(int $id, AttachCategoriesCourseAPIRequest $attachCategoriesCourseAPIRequest);

    /**
     * @OA\Post(
     *      path="/api/courses/attach/{id}/tags",
     *      summary="Attach tags for couse",
     *      tags={"Course"},
     *      description="Attach tags for couse",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="tags",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         type="array",
     *                         @OA\Items(
     *                           @OA\Property(
     *                                 property="title",
     *                                 type="string",
     *                                 example="Nowości"
     *                              ),
     *                          ),
     *                      ),
     *                  ),
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          ),
     *      )
     * )
     */

    public function attachTags(int $id, AttachTagsCourseAPIRequest $attachTagsCourseAPIRequest);

    /**
     * @OA\Get(
     *      tags={"Courses"},
     *      path="/api/courses/search/tags",
     *      description="Searche Course By Criteria",
     *      operationId="searchCourseByCategory",
     *      @OA\Parameter(
     *          name="tag",
     *          in="path",
     *          @OA\Schema(
     *              type="string",
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

    public function searchByTag(Request $request);
}