<?php

use EscolaLms\Courses\Http\Controllers\CourseAPIController;
use EscolaLms\Courses\Http\Controllers\CourseProgressAPIController;
use EscolaLms\Courses\Http\Controllers\LessonAPIController;
use EscolaLms\Courses\Http\Controllers\TopicAPIController;

// endpoints
Route::group(['middleware' => ['auth:api'], 'prefix' => 'api'], function () {
    Route::group(['prefix' => '/courses/progress'], function () {
        Route::get('/', [CourseProgressAPIController::class, 'index']);
        Route::get('/{course_id}', [CourseProgressAPIController::class, 'show']);
        Route::patch('/{course_id}', [CourseProgressAPIController::class, 'store']);

        Route::put('/{topic_id}/ping', [CourseProgressAPIController::class, 'ping']);
        Route::post('/{topic_id}/h5p', [CourseProgressAPIController::class, 'h5p']);
    });
    Route::get('courses/{course}/program', [CourseAPIController::class, 'program']);
    Route::post('courses/sort', [CourseAPIController::class, "sort"]);
    Route::post('courses/{course}', [CourseAPIController::class, 'update']);
    Route::resource('courses', CourseAPIController::class);
    Route::resource('lessons', LessonAPIController::class);
    Route::get('topics/types', [TopicAPIController::class, 'classes']);
    Route::resource('topics', TopicAPIController::class);
    Route::post('topics/{topic}', [TopicAPIController::class, "update"]);


    Route::get('/courses/search/tags', [CourseAPIController::class, 'searchByTag']);
    Route::get('/courses/search/{category_id}', [CourseAPIController::class, 'category']);
    Route::group(['prefix' => '/courses/attach/{id}/'], function () {
        Route::post('categories', [CourseAPIController::class, 'attachCategory']);
        Route::post('tags', [CourseAPIController::class, 'attachTags']);
    });
});

// public routes
Route::group(['prefix' => 'api'], function () {
    Route::get('/courses', [CourseAPIController::class, 'index']);
    Route::get('/courses/{id}', [CourseAPIController::class, 'show']);
});
