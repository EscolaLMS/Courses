<?php

use EscolaLms\Courses\Http\Controllers\CourseAccessAPIController;
use EscolaLms\Courses\Http\Controllers\CourseAPIController;
use EscolaLms\Courses\Http\Controllers\CourseAuthorsAPIController;
use EscolaLms\Courses\Http\Controllers\CourseProgressAPIController;
use EscolaLms\Courses\Http\Controllers\LessonAPIController;
use EscolaLms\Courses\Http\Controllers\TopicAPIController;
use EscolaLms\Courses\Http\Controllers\TopicResourcesAPIController;
use EscolaLms\Tags\Http\Controllers\TagsAPIController;
use Illuminate\Support\Facades\Route;

// admin endpoints
Route::group(['middleware' => ['auth:api'], 'prefix' => 'api/admin'], function () {
    Route::get('courses/{course}/program', [CourseAPIController::class, 'program']);
    Route::post('courses/sort', [CourseAPIController::class, "sort"]);
    Route::post('courses/{course}', [CourseAPIController::class, 'update']);
    Route::resource('courses', CourseAPIController::class);
    Route::resource('lessons', LessonAPIController::class);
    Route::get('topics/types', [TopicAPIController::class, 'classes']);
    Route::resource('topics', TopicAPIController::class);
    Route::post('topics/{topic}', [TopicAPIController::class, "update"]);
    Route::post('topics/{id}/clone', [TopicAPIController::class, 'clone']);

    Route::get('topics/{topic_id}/resources/', [TopicResourcesAPIController::class, 'list']);
    Route::post('topics/{topic_id}/resources/', [TopicResourcesAPIController::class, 'upload']);
    Route::patch('topics/{topic_id}/resources/{resource_id}', [TopicResourcesAPIController::class, 'rename']);
    Route::delete('topics/{topic_id}/resources/{resource_id}', [TopicResourcesAPIController::class, 'delete']);

    Route::get('courses/{course_id}/access', [CourseAccessAPIController::class, 'list']);
    Route::post('courses/{course_id}/access/add', [CourseAccessAPIController::class, 'add']);
    Route::post('courses/{course_id}/access/remove', [CourseAccessAPIController::class, 'remove']);
    Route::post('courses/{course_id}/access/set', [CourseAccessAPIController::class, 'set']);

    Route::get('/courses/search/tags', [CourseAPIController::class, 'searchByTag']);
    Route::get('/courses/search/{category_id}', [CourseAPIController::class, 'category']);
});

// user endpoints
Route::group(['middleware' => ['auth:api'], 'prefix' => 'api'], function () {
    Route::group(['prefix' => '/courses/progress'], function () {
        Route::get('/', [CourseProgressAPIController::class, 'index']);
        Route::get('/{course_id}', [CourseProgressAPIController::class, 'show']);
        Route::patch('/{course_id}', [CourseProgressAPIController::class, 'store']);

        Route::put('/{topic_id}/ping', [CourseProgressAPIController::class, 'ping']);
        Route::post('/{topic_id}/h5p', [CourseProgressAPIController::class, 'h5p']);
    });
});

// public routes
Route::group(['prefix' => 'api'], function () {
    Route::get('courses/{course}/program', [CourseAPIController::class, 'program']); // when course is free, it doesnt need token
    Route::get('courses/{course}/scorm', [CourseAPIController::class, 'scorm']); // when course is free, it doesnt need token
    Route::get('/courses', [CourseAPIController::class, 'index']);
    Route::get('/courses/{course}', [CourseAPIController::class, 'show']);
    Route::get('/tutors', [CourseAuthorsAPIController::class, 'index']);
    Route::get('/tutors/{id}', [CourseAuthorsAPIController::class, 'show']);

    Route::group(['prefix' => 'tags'], function () {
        Route::get('uniqueTags', [CourseAPIController::class, 'uniqueTags']);
    });
});
