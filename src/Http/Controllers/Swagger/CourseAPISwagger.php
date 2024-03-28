<?php

namespace EscolaLms\Courses\Http\Controllers\Swagger;


use EscolaLms\Courses\Http\Requests\CreateCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\ListAuthoredCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\ListCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\DeleteCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\GetCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\GetCourseCurriculumAPIRequest;
use EscolaLms\Courses\Http\Requests\SortAPIRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface CourseAPISwagger
{
    /**
     * @OA\Get(
     *      path="/api/courses",
     *      summary="Get a listing of the Courses.",
     *      tags={"Courses"},
     *      description="Get all Courses",
     *      @OA\Parameter(
     *          name="order_by",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              enum={"created_at","title","duration","only_with_categories"}
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="order",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              enum={"ASC", "DESC"}
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="only_with_categories",
     *          description="Consultation has categories",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="boolean",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          description="Pagination Page Number",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="number",
     *               default=1,
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Pagination Per Page",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="number",
     *               default=15,
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="title",
     *          description="Course title %LIKE%",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="category_id",
     *          description="Category ID. When applied all courses with given cat_id and children categories are searched. Prohibited with categories",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="number",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="categories[]",
     *          description="An array of categories IDs. When applied all courses with given categories ids and children categories are searched. Prohibited with category_id",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="array", @OA\Items(type="number")),
     *      ),
     *      @OA\Parameter(
     *          name="authors[]",
     *          description="An array of author IDs",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="array", @OA\Items(type="number")),
     *      ),
     *      @OA\Parameter(
     *          name="tag",
     *          description="Tag name exactly",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="tag[]",
     *          description="An array of tags",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="array", @OA\Items(type="string")),
     *      ),
     *      @OA\Parameter(
     *          name="ids[]",
     *          description="An array of IDs",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="array", @OA\Items(type="number")),
     *      ),
     *      @OA\Parameter(
     *          name="free",
     *          description="Will show only free courses",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="boolean"),
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
     *                  @OA\Items(ref="#/components/schemas/Course")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * ),
     * @OA\Get(
     *      path="/api/admin/courses",
     *      summary="Get a listing of the Courses.",
     *      tags={"Admin Courses"},
     *      description="Get all Courses",
     *      security={
     *         {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="order_by",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              enum={"created_at","title","duration"}
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="order",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              enum={"ASC", "DESC"}
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          description="Pagination Page Number",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="number",
     *               default=1,
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Pagination Per Page",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="number",
     *               default=15,
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="title",
     *          description="Course title %LIKE%",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="category_id",
     *          description="Category ID. When applied all courses with given cat_id and children categories are searched",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="number",
     *          ),
     *      ),
     *     @OA\Parameter(
     *          name="authors",
     *          description="Authors",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *                 ref="#/components/schemas/User"
     *              )
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="tag",
     *          description="Tag name exactly",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="free",
     *          description="Will show only free courses",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="boolean"),
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          description="Course status: published, draft, archived",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *     @OA\Parameter(
     *          name="group_id",
     *          description="Group",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          ),
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

    public function index(ListCourseAPIRequest $request);

    /**
     * @OA\Get(
     *      path="/api/courses/authored",
     *      summary="Get a listing of the authored Courses.",
     *      tags={"Courses"},
     *      description="Get all Courses",
     *      security={
     *         {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="page",
     *          description="Pagination Page Number",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="number",
     *               default=1,
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Pagination Per Page",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="number",
     *               default=15,
     *          ),
     *      ),
     *     @OA\Parameter(
     *           name="order_by",
     *           description="Order by column",
     *           required=false,
     *           in="query",
     *           @OA\Schema(
     *               type="string",
     *                enum={"created_at","title","id", "status"}
     *           ),
     *       ),
     *     @OA\Parameter(
     *           name="order",
     *           description="Order direction",
     *           required=false,
     *           in="query",
     *           @OA\Schema(
     *               type="string",
     *                enum={"ASC","DESC"}
     *           ),
     *       ),
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

    public function authoredCourses(ListAuthoredCourseAPIRequest $request);

    /**
     * @OA\Post(
     *      path="/api/admin/courses",
     *      summary="Store a newly created Course in storage",
     *      tags={"Admin Courses"},
     *      description="Store Course",
     *     security={
     *         {"passport": {}},
     *     },
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/Course")
     *          ),
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
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
     *      path="/api/admin/courses/{id}",
     *      summary="Display the specified Course",
     *      tags={"Admin Courses"},
     *      description="Get Course",
     *      security={
     *         {"passport": {}},
     *      },
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

    /**
     * @OA\Get(
     *      path="/api/courses/{id}",
     *      summary="Display the specified Course",
     *      tags={"Courses"},
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

    public function show($id, GetCourseAPIRequest $request);

    /**
     * @OA\Get(
     *      path="/api/admin/courses/{id}/program",
     *      summary="Display the specified Course program/curriculum/syllabus",
     *      tags={"Admin Courses"},
     *      description="Get Course",
     *     security={
     *         {"passport": {}},
     *     },
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

    /**
     * @OA\Get(
     *      path="/api/courses/{id}/program",
     *      summary="Display the specified Course program/curriculum/syllabus. No token required when course is free",
     *      tags={"Courses"},
     *      description="Get Course",
     *      security={
     *         {"passport": {}},
     *      },
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

    public function scorm($id, GetCourseCurriculumAPIRequest $request);

    /**
     * @OA\Get(
     *      path="/api/courses/{id}/scorm",
     *      summary="Display the specified Course scorm View to be embeede with iframe",
     *      tags={"Courses"},
     *      description="Get Course",
     *      security={
     *         {"passport": {}},
     *      },
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
     *              mediaType="text/html"
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

    public function program($id, GetCourseCurriculumAPIRequest $request);

    /**
     * @OA\Put(
     *      path="/api/admin/courses/{id}",
     *      summary="Update the specified Course in storage",
     *      tags={"Admin Courses"},
     *      description="Update Course",
     *     security={
     *         {"passport": {}},
     *     },
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

    /**
     * @OA\Post(
     *      path="/api/admin/courses/{id}",
     *      summary="Update the specified Course in storage",
     *      tags={"Admin Courses"},
     *      description="Update Course",
     *     security={
     *         {"passport": {}},
     *     },
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
     *              mediaType="multipart/form-data",
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
     *      path="/api/admin/courses/{id}",
     *      summary="Remove the specified Course from storage",
     *      tags={"Admin Courses"},
     *      description="Delete Course",
     *     security={
     *         {"passport": {}},
     *     },
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

    public function destroy($id, DeleteCourseAPIRequest $request);

    /**
     * @OA\Post(
     *      path="/api/admin/courses/sort",
     *      summary="Sorts Lessons or Topics",
     *      tags={"Admin Courses"},
     *      description="Sorts Lessons or Topics by sending course_id, class (Topic or Lesson) and array of tuple [class_id, order]. Example
     * `{""class"":""Lesson"",""orders"":[[3,0],[2,1],[4,2],[5,3],[6,4],[7,5],[1,6],[71,7]], ""course_id"":1}`
     * ",
     *     security={
     *         {"passport": {}},
     *     },
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="course_id",
     *                  type="integer",
     *              ),
     *              @OA\Property(
     *                  property="class",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="orders",
     *                  type="array",
     *                  @OA\Items(
     *                      type="array",
     *                      @OA\Items(type="integer"),
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

    public function sort(SortAPIRequest $request);

    /**
     * @OA\Get(
     *      path="/api/tags/uniqueTags",
     *      summary="Endpoints get unique tags from active courses",
     *      tags={"Courses"},
     *      description="Get unique tags",
     *      security={
     *         {"passport": {}},
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="text/html"
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
    public function uniqueTags(): JsonResponse;
}
