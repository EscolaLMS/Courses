<?php

use EscolaLms\Courses\Http\Controllers\CourseAPIController;
use EscolaLms\Courses\Http\Controllers\LessonAPIController;
use EscolaLms\Courses\Http\Controllers\TopicAPIController;
use EscolaLms\Courses\Http\Controllers\TopicRichTextAPIController;
use Illuminate\Routing\ImplicitRouteBinding;

Route::group(['middleware' => ['api'], 'prefix' => 'api'], function () {
    Route::resource('courses', CourseAPIController::class);
    Route::resource('lessons', LessonAPIController::class);
    Route::resource('topics', TopicAPIController::class);
    Route::resource('topic_rich_texts', TopicRichTextAPIController::class);

    Route::get('/courses/search/tags', [CourseAPIController::class, 'searchByTag']);
    Route::get('/courses/search/{category_id}', [CourseAPIController::class, 'category']);
    Route::group(['prefix' => '/courses/attach/{id}/'], function () {
        Route::post('categories', [CourseAPIController::class, 'attachCategory']);
        Route::post('tags', [CourseAPIController::class, 'attachTags']);
    });
});
