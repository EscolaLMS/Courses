<?php

Route::group(['middleware' => ['api'], 'prefix' => 'api'], function () {
    Route::resource('courses', App\Http\Controllers\API\CourseAPIController::class);
    Route::resource('lessons', App\Http\Controllers\API\LessonAPIController::class);
    Route::resource('topics', App\Http\Controllers\API\TopicAPIController::class);
});
