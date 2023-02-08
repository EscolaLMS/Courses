<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnParentLessonIdToLessonsTable extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->unsignedBigInteger('course_id')->nullable()->change();

            $table->unsignedBigInteger('parent_lesson_id')->nullable();
            $table->foreign('parent_lesson_id')->references('id')->on('lessons');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->unsignedBigInteger('course_id')->nullable(false)->change();

            $table->dropColumn(['parent_lesson_id']);
        });
    }
}
