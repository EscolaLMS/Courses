<?php

namespace EscolaLms\Courses\Http\Controllers\Swagger;

use EscolaLms\Courses\Http\Requests\CourseProgressAPIRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface CourseAuthorsAPISwagger
{
    /**
     * @OA\Get(
     *      tags={"Courses"},
     *      path="/api/tutors",
     *      description="Get Tutors",
     *      security={
     *          {"passport": {}},
     *      },
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
    public function index(Request $request): JsonResponse;

    /**
     * @OA\Get(
     *      tags={"Courses"},
     *      path="/api/tutors/{id}",
     *      description="Get tutor course/author ID ",
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
    public function show($id, Request $request): JsonResponse;
}
