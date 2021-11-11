<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\Courses\Http\Controllers\Swagger\CourseExportImportAPISwagger;
use EscolaLms\Courses\Http\Requests\UpdateCourseAPIRequest;
use EscolaLms\Courses\Services\Contracts\ExportImportServiceContract;
use Illuminate\Http\JsonResponse;

class CourseExportImportAPIController extends EscolaLmsBaseController implements CourseExportImportAPISwagger
{
    protected ExportImportServiceContract $exportImportService;

    public function __construct(
        ExportImportServiceContract $exportImportService
    ) {
        $this->exportImportService = $exportImportService;
    }

    public function export(int $course_id, UpdateCourseAPIRequest $request): JsonResponse
    {
        $export = $this->exportImportService->export($course_id);

        return $this->sendResponse($export, __('Export created'));
    }
}
