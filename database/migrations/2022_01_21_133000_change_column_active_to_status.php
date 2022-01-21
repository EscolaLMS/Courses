<?php

use EscolaLms\Courses\Enum\CourseStatusEnum;
use EscolaLms\Courses\Models\Course;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnActiveToStatus extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('status')->default(CourseStatusEnum::PUBLISHED);
        });

        foreach (Course::all() as $course) {
            $course->status = $course->active ? CourseStatusEnum::PUBLISHED : CourseStatusEnum::ARCHIVED;
            $course->save();
        }

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('active')->default(true);
        });

        foreach (Course::all() as $course) {
            $course->active = $course->status === CourseStatusEnum::PUBLISHED;
            $course->save();
        }

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
