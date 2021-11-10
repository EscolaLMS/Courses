<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Courses\Http\Controllers\Swagger\LessonAPISwagger;
use EscolaLms\Courses\Http\Requests\CreateLessonAPIRequest;
use EscolaLms\Courses\Http\Requests\DeleteLessonAPIRequest;
use EscolaLms\Courses\Http\Requests\GetLessonAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateLessonAPIRequest;
use EscolaLms\Courses\Http\Resources\LessonResource;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Repositories\LessonRepository;
use Illuminate\Http\Request;

/**
 * Class LessonController.
 */
class LessonAPIController extends AppBaseController implements LessonAPISwagger
{
    /** @var LessonRepository */
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

        $lesson = $this->lessonRepository->create($input);

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

        $lesson = $this->lessonRepository->update($input, $id);

        return $this->sendResponseForResource(LessonResource::make($lesson), 'Lesson updated successfully');
    }

    public function destroy($id, DeleteLessonAPIRequest $request)
    {
        $lesson = $request->getLesson();

        if (empty($lesson)) {
            return $this->sendError('Lesson not found');
        }

        $this->lessonRepository->delete($id);

        return $this->sendSuccess('Lesson deleted successfully');
    }
}
