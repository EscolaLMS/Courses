<?php

use EscolaLms\Courses\Http\Controllers\CourseAPIController;
use EscolaLms\Courses\Http\Controllers\CourseProgressAPIController;
use EscolaLms\Courses\Http\Controllers\LessonAPIController;
use EscolaLms\Courses\Http\Controllers\TopicAPIController;
use EscolaLms\Courses\Http\Controllers\TopicRichTextAPIController;
use Illuminate\Routing\ImplicitRouteBinding;

Route::group(['middleware' => ['api'], 'prefix' => 'api'], function () {
    Route::get('courses/{id}/program', [CourseAPIController::class, 'program']);
    Route::post('courses/{id}', [CourseAPIController::class, 'update']);
    Route::resource('courses', CourseAPIController::class);
    Route::resource('lessons', LessonAPIController::class);
    Route::get('topics/types', [TopicAPIController::class, 'classes']);
    Route::post('topics/{id}', [TopicAPIController::class, 'update']);
    Route::resource('topics', TopicAPIController::class);
    Route::resource('topic_rich_texts', TopicRichTextAPIController::class);

    Route::get('/courses/search/tags', [CourseAPIController::class, 'searchByTag']);
    Route::get('/courses/search/{category_id}', [CourseAPIController::class, 'category']);
    Route::group(['prefix' => '/courses/attach/{id}/'], function () {
        Route::post('categories', [CourseAPIController::class, 'attachCategory']);
        Route::post('tags', [CourseAPIController::class, 'attachTags']);
    });


    Route::group(['prefix' => '/progress'], function () {
        Route::get('/', [CourseProgressAPIController::class, 'index']);
        Route::get('/{course}', [CourseProgressAPIController::class, 'show']);
        Route::patch('/{course}', [CourseProgressAPIController::class, 'store']);
        Route::put('/{curriculum_lectures_quiz}/ping', [CourseProgressAPIController::class, 'ping']);
        Route::post('/{curriculum_lectures_quiz}/h5p', [CourseProgressAPIController::class, 'h5p']);
    });
});
