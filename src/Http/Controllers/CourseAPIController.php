<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Categories\Models\Category;
use EscolaLms\Categories\Repositories\Contracts\CategoriesRepositoryContract;
use EscolaLms\Courses\Dto\CourseSearchDto;
use EscolaLms\Courses\Http\Requests\AttachCategoriesCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\AttachTagsCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\CreateCourseAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateCourseAPIRequest;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use EscolaLms\Courses\Repositories\CourseRepository;
use EscolaLms\Courses\Services\Contracts\CourseServiceContract;
use Illuminate\Http\Request;
use EscolaLms\Courses\Http\Controllers\AppBaseController;
use Response;

/**
 * Class CourseController
 * @package App\Http\Controllers
 */
class CourseAPIController extends AppBaseController
{
    /** @var  CourseRepository */
    private CourseRepositoryContract $courseRepository;
    private CourseServiceContract $courseServiceContract;
    private CategoriesRepositoryContract $categoriesRepositoryContract;

    public function __construct(
        CourseRepositoryContract $courseRepo,
        CourseServiceContract $courseServiceContract,
        CategoriesRepositoryContract $categoriesRepositoryContract
    )
    {
        $this->courseRepository = $courseRepo;
        $this->courseServiceContract = $courseServiceContract;
        $this->categoriesRepositoryContract = $categoriesRepositoryContract;
    }

    /**
     * @param Request $request
     * @return Response
     *
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

    public function index(Request $request)
    {
        $courses = $this->courseRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($courses->toArray(), 'Courses retrieved successfully');
    }

    /**
     * @param CreateCourseAPIRequest $request
     * @return Response
     *
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

    public function store(CreateCourseAPIRequest $request)
    {
        $input = $request->all();

        $course = $this->courseRepository->create($input);

        return $this->sendResponse($course->toArray(), 'Course saved successfully');
    }

    /**
     * @param int $id
     * @return Response
     *
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

    public function show($id)
    {
        /** @var Course $course */
        $course = $this->courseRepository->find($id);

        if (empty($course)) {
            return $this->sendError('Course not found');
        }

        return $this->sendResponse($course->toArray(), 'Course retrieved successfully');
    }

    /**
     * @param int $id
     * @param UpdateCourseAPIRequest $request
     * @return Response
     *
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

    public function update($id, UpdateCourseAPIRequest $request)
    {
        $input = $request->all();

        /** @var Course $course */
        $course = $this->courseRepository->find($id);

        if (empty($course)) {
            return $this->sendError('Course not found');
        }

        $course = $this->courseRepository->update($input, $id);

        return $this->sendResponse($course->toArray(), 'Course updated successfully');
    }

    /**
     * @param int $id
     * @return Response
     *
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

    public function destroy($id)
    {
        /** @var Course $course */
        $course = $this->courseRepository->find($id);

        if (empty($course)) {
            return $this->sendError('Course not found');
        }

        $course->delete();

        return $this->sendSuccess('Course deleted successfully');
    }

    /**
     * @param int $category_id
     * @param Request $request
     * @return mixed
     *
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

    public function category(int $category_id, Request $request)
    {
        $category = $this->categoriesRepositoryContract->find($category_id);
        $courses = $this->courseServiceContract->searchInCategoryAndSubCategory($category);
        return $this->sendResponse($courses->toArray(), 'Course updated successfully');
    }

    /**
     * @param int $id
     * @param AttachCategoriesCourseAPIRequest $attachCategoriesCourseAPIRequest
     * @return mixed
     *
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

    public function attachCategory(int $id, AttachCategoriesCourseAPIRequest $attachCategoriesCourseAPIRequest)
    {
        /** @var Course $course */
        $course = $this->courseRepository->find($id);
        $this->courseServiceContract->attachCategories($course, $attachCategoriesCourseAPIRequest->input('categories'));

        return $this->sendResponse([], 'Course updated successfully');
    }

    /**
     * @param int $id
     * @param AttachTagsCourseAPIRequest $attachTagsCourseAPIRequest
     * @return mixed
     *
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

    public function attachTags(int $id, AttachTagsCourseAPIRequest $attachTagsCourseAPIRequest)
    {
        /** @var Course $course */
        $course = $this->courseRepository->find($id);

        $this->courseServiceContract->attachTags($course, $attachTagsCourseAPIRequest->input('tags'));
        return $this->sendResponse([], 'Course updated successfully');
    }

    /**
     * @param Request $request
     * @return mixed
     *
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

    public function searchByTag(Request $request)
    {
        $courses = $this->courseRepository
            ->allQueryBuilder($request->only('tag'))
            ->orderBy('courses.id', 'desc')
            ->paginate();

        return $this->sendResponse($courses->toArray(), 'Course updated successfully');
    }
}

