<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CourseUserAttendance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_user_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_progress_id');
            $table->foreign('course_progress_id')->references('id')->on('course_progress')->onDelete('cascade');
            $table->dateTime('attendance_date');
            $table->integer('attempt')->default(0);
        });

        Schema::table('course_progress', function (Blueprint $table) {
            $table->integer('attempt')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_user_attendances');

        Schema::table('course_progress', function (Blueprint $table) {
            $table->dropColumn('attempt');
        });
    }
}
