<?php

use EscolaLms\Courses\Http\Controllers\CourseAPIController;
use EscolaLms\Courses\Http\Controllers\CourseAuthorsAPIController;
use EscolaLms\Courses\Http\Controllers\CourseProgressAPIController;
use EscolaLms\Courses\Http\Controllers\LessonAPIController;
use EscolaLms\Courses\Http\Controllers\TopicAPIController;
use EscolaLms\Courses\Http\Controllers\TopicResourcesAPIController;
use Illuminate\Support\Facades\Route;

// admin endpoints
Route::group(['middleware' => ['auth:api'], 'prefix' => 'api/admin'], function () {
    Route::get('courses/{course}/program', [CourseAPIController::class, 'program'])->middleware('cacheResponse');
    Route::post('courses/sort', [CourseAPIController::class, "sort"]);
    Route::post('courses/{course}', [CourseAPIController::class, 'update']);
    Route::resource('courses', CourseAPIController::class);
    Route::resource('lessons', LessonAPIController::class);
    Route::post('lessons/{id}/clone', [LessonAPIController::class, 'clone']);
    Route::get('topics/types', [TopicAPIController::class, 'classes']);
    Route::resource('topics', TopicAPIController::class);
    Route::post('topics/{topic}', [TopicAPIController::class, "update"]);
    Route::post('topics/{id}/clone', [TopicAPIController::class, 'clone']);

    Route::get('topics/{topic_id}/resources/', [TopicResourcesAPIController::class, 'list'])->middleware('cacheResponse');
    Route::post('topics/{topic_id}/resources/', [TopicResourcesAPIController::class, 'upload']);
    Route::patch('topics/{topic_id}/resources/{resource_id}', [TopicResourcesAPIController::class, 'rename']);
    Route::delete('topics/{topic_id}/resources/{resource_id}', [TopicResourcesAPIController::class, 'delete']);

    Route::get('/courses/search/tags', [CourseAPIController::class, 'searchByTag']);
    Route::get('/courses/search/{category_id}', [CourseAPIController::class, 'category']);
    Route::get('/courses/users/assignable', [CourseAuthorsAPIController::class, 'assignableUsers']);

    Route::post('/tutors/{id}/assign/{course}', [CourseAuthorsAPIController::class, 'assign']);
    Route::post('/tutors/{id}/unassign/{course}', [CourseAuthorsAPIController::class, 'unassign']);
});

// user endpoints
Route::group(['prefix' => 'api/courses'], function () {
    Route::get('/{course}/program', [CourseAPIController::class, 'program'])->middleware('cacheResponse');
    Route::get('/{course}/preview/{topic_id}', [CourseAPIController::class, 'preview']);

    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('/{course}/scorm', [CourseAPIController::class, 'scorm']);
        Route::group(['prefix' => '/progress'], function () {
            Route::get('/paginated', [CourseProgressAPIController::class, 'indexPaginated']);
            Route::get('/', [CourseProgressAPIController::class, 'index'])->middleware('cacheResponse');
            Route::get('/{course_id}', [CourseProgressAPIController::class, 'show']);
            Route::patch('/{course_id}', [CourseProgressAPIController::class, 'store']);
            Route::put('/{topic_id}/ping', [CourseProgressAPIController::class, 'ping'])->whereNumber('topic_id');
            Route::post('/{topic_id}/h5p', [CourseProgressAPIController::class, 'h5p']);
        });
    });
});

// public routes
Route::group(['prefix' => 'api'], function () {
    Route::group(['middleware' => ['auth:api', 'cacheResponse']], function () {
        Route::get('/courses/authored', [CourseAPIController::class, 'authoredCourses']);
    });

    Route::get('/courses', [CourseAPIController::class, 'index'])->middleware('cacheResponse');
    Route::get('/courses/{course}', [CourseAPIController::class, 'show'])->middleware('cacheResponse');
    Route::get('/tutors', [CourseAuthorsAPIController::class, 'index']);
    Route::get('/tutors/{id}', [CourseAuthorsAPIController::class, 'show']);

    Route::group(['prefix' => 'tags'], function () {
        Route::get('uniqueTags', [CourseAPIController::class, 'uniqueTags']);
    });
});
