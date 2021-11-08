<?php

namespace EscolaLms\Courses\Http\Controllers;

use Error;
use EscolaLms\Courses\Exceptions\TopicException;
use EscolaLms\Courses\Http\Controllers\Swagger\LessonAPISwagger;
use EscolaLms\Courses\Http\Requests\CreateLessonAPIRequest;
use EscolaLms\Courses\Http\Requests\DeleteLessonAPIRequest;
use EscolaLms\Courses\Http\Requests\GetLessonAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateLessonAPIRequest;
use EscolaLms\Courses\Http\Resources\LessonResource;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Repositories\LessonRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class LessonController
 * @package App\Http\Controllers
 */

class LessonAPIController extends AppBaseController implements LessonAPISwagger
{
    /** @var  LessonRepository */
    private $lessonRepository;

    public function __construct(LessonRepository $lessonRepo)
    {
        $this->lessonRepository = $lessonRepo;
    }

    public function index(Request $request)
    {
        $lessons = $this->lessonRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponseForResource(LessonResource::collection($lessons), 'Lessons retrieved successfully');
    }

    public function store(CreateLessonAPIRequest $request)
    {
        $input = $request->all();

        try {
            $lesson = $this->lessonRepository->create($input);
        } catch (AccessDeniedHttpException $error) {
            return $this->sendError($error->getMessage(), 403);
        } catch (TopicException $error) {
            return $this->sendDataError($error->getMessage(), $error->getData());
        } catch (Error $error) {
            return $this->sendError($error->getMessage(), 422);
        }

        return $this->sendResponseForResource(LessonResource::make($lesson), 'Lesson saved successfully');
    }

    public function show($id, GetLessonAPIRequest $request)
    {
        $lesson = $request->getLesson();

        if (empty($lesson)) {
            return $this->sendError('Lesson not found');
        }

        return $this->sendResponseForResource(LessonResource::make($lesson), 'Lesson retrieved successfully');
    }

    public function update($id, UpdateLessonAPIRequest $request)
    {
        $input = $request->all();

        /** @var Lesson $lesson */
        $lesson = $this->lessonRepository->find($id);

        if (empty($lesson)) {
            return $this->sendError('Lesson not found');
        }

        try {
            $lesson = $this->lessonRepository->update($input, $id);
        } catch (AccessDeniedHttpException $error) {
            return $this->sendError($error->getMessage(), 403);
        } catch (TopicException $error) {
            return $this->sendDataError($error->getMessage(), $error->getData());
        } catch (Error $error) {
            return $this->sendError($error->getMessage(), 422);
        }

        return $this->sendResponseForResource(LessonResource::make($lesson), 'Lesson updated successfully');
    }

    public function destroy($id, DeleteLessonAPIRequest $request)
    {
        $lesson = $request->getLesson();

        if (empty($lesson)) {
            return $this->sendError('Lesson not found');
        }

        try {
            $this->courseRepository->delete($id);
        } catch (AccessDeniedHttpException $error) {
            return $this->sendError($error->getMessage(), 403);
        } catch (TopicException $error) {
            return $this->sendDataError($error->getMessage(), $error->getData());
        } catch (Error $error) {
            return $this->sendError($error->getMessage(), 422);
        }

        return $this->sendSuccess('Lesson deleted successfully');
    }
}
