<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Courses\Http\Controllers\Swagger\LessonAPISwagger;
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

        return $this->sendResponse($lessons->toArray(), 'Lessons retrieved successfully');
    }

    public function store(CreateLessonAPIRequest $request)
    {
        $input = $request->all();

        $lesson = $this->lessonRepository->create($input);

        return $this->sendResponse($lesson->toArray(), 'Lesson saved successfully');
    }

    public function show($id)
    {
        /** @var Lesson $lesson */
        $lesson = $this->lessonRepository->find($id);

        if (empty($lesson)) {
            return $this->sendError('Lesson not found');
        }

        return $this->sendResponse($lesson->toArray(), 'Lesson retrieved successfully');
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

        return $this->sendResponse($lesson->toArray(), 'Lesson updated successfully');
    }

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
