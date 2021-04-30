<?php

use EscolaLms\Courses\Http\Controllers\CourseAPIController;
use EscolaLms\Courses\Http\Controllers\LessonAPIController;
use EscolaLms\Courses\Http\Controllers\TopicAPIController;
use EscolaLms\Courses\Http\Controllers\TopicRichTextAPIController;

Route::group(['middleware' => ['api'], 'prefix' => 'api'], function () {
    Route::resource('courses', CourseAPIController::class);
    Route::resource('lessons', LessonAPIController::class);
    Route::get('topics/types', [TopicAPIController::class, 'classes']);
    Route::resource('topics', TopicAPIController::class);
    Route::resource('topic_rich_texts', TopicRichTextAPIController::class);
});
