<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Courses\Http\Requests\CreateLessonAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateLessonAPIRequest;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Repositories\LessonRepository;
use Illuminate\Http\Request;
use EscolaLms\Courses\Http\Controllers\AppBaseController;
use Response;

/**
 * Class LessonController
 * @package App\Http\Controllers
 */

class LessonAPIController extends AppBaseController
{
    /** @var  LessonRepository */
    private $lessonRepository;

    public function __construct(LessonRepository $lessonRepo)
    {
        $this->lessonRepository = $lessonRepo;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @OA\Get(
     *      path="/api/lessons",
     *      summary="Get a listing of the Lessons.",
     *      tags={"Lesson"},
     *      description="Get all Lessons",
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

    public function index(Request $request)
    {
        $lessons = $this->lessonRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($lessons->toArray(), 'Lessons retrieved successfully');
    }

    /**
     * @param CreateLessonAPIRequest $request
     * @return Response
     *
     * @OA\Post(
     *      path="/api/lessons",
     *      summary="Store a newly created Lesson in storage",
     *      tags={"Lesson"},
     *      description="Store Lesson",
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

    public function store(CreateLessonAPIRequest $request)
    {
        $input = $request->all();

        $lesson = $this->lessonRepository->create($input);

        return $this->sendResponse($lesson->toArray(), 'Lesson saved successfully');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @OA\Get(
     *      path="/api/lessons/{id}",
     *      summary="Display the specified Lesson",
     *      tags={"Lesson"},
     *      description="Get Lesson",
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

    public function show($id)
    {
        /** @var Lesson $lesson */
        $lesson = $this->lessonRepository->find($id);

        if (empty($lesson)) {
            return $this->sendError('Lesson not found');
        }

        return $this->sendResponse($lesson->toArray(), 'Lesson retrieved successfully');
    }

    /**
     * @param int $id
     * @param UpdateLessonAPIRequest $request
     * @return Response
     *
     * @OA\Put(
     *      path="/api/lessons/{id}",
     *      summary="Update the specified Lesson in storage",
     *      tags={"Lesson"},
     *      description="Update Lesson",
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

    public function update($id, UpdateLessonAPIRequest $request)
    {
        $input = $request->all();

        /** @var Lesson $lesson */
        $lesson = $this->lessonRepository->find($id);

        if (empty($lesson)) {
            return $this->sendError('Lesson not found');
        }

        $lesson = $this->lessonRepository->update($input, $id);

        return $this->sendResponse($lesson->toArray(), 'Lesson updated successfully');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @OA\Delete(
     *      path="/api/lessons/{id}",
     *      summary="Remove the specified Lesson from storage",
     *      tags={"Lesson"},
     *      description="Delete Lesson",
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

    public function destroy($id)
    {
        /** @var Lesson $lesson */
        $lesson = $this->lessonRepository->find($id);

        if (empty($lesson)) {
            return $this->sendError('Lesson not found');
        }

        $lesson->delete();

        return $this->sendSuccess('Lesson deleted successfully');
    }
}
