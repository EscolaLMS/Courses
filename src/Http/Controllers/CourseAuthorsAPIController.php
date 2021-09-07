<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Courses\Http\Controllers\Swagger\CourseAuthorsAPISwagger;
use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseAuthorsAPIController extends AppBaseController implements CourseAuthorsAPISwagger
{
    protected CourseRepositoryContract $courseRepositoryContract;

    public function __construct(
        CourseRepositoryContract $courseRepositoryContract
    ) {
        $this->courseRepositoryContract = $courseRepositoryContract;
    }

    public function index(Request $request): JsonResponse
    {
        $tutors = $this->courseRepositoryContract->findTutors();
        return $this->sendResponse($tutors->toArray(), 'Tutors retrieved successfully');
    }

    /**
     * Display the specified CourseProgress.
     */
    public function show($id, Request $request): JsonResponse
    {
        try {
            $tutor = $this->courseRepositoryContract->findTutor($id);
        } catch (\Exception $error) {
            return $this->sendError($error->getMessage(), 404);
        }
        if (empty($tutor)) {
            return $this->sendError('Not found', 404);
        }
        return $this->sendResponse($tutor, 'Tutor retrieved successfully');
    }
}
