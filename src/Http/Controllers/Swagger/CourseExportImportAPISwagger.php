<?php

namespace EscolaLms\Courses\Http\Controllers\Swagger;

use EscolaLms\Courses\Http\Requests\UpdateCourseAPIRequest;
use Illuminate\Http\JsonResponse;

interface CourseExportImportAPISwagger
{
    /**
     * @OA\Get(
     *      tags={"Admin Courses"},
     *      path="/api/admin/courses/{id}/export",
     *      description="Exports course to ZIP package ",
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="id",
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
    public function export(int $course_id, UpdateCourseAPIRequest $request): JsonResponse;
}
