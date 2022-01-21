<?php

use EscolaLms\Courses\Models\Course;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateSingleAuthorToMultiple extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (Course::all() as $course) {
            if ($course->author_id) {
                $course->authors()->syncWithoutDetaching([$course->author_id]);
            }
        }
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('author_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->bigInteger('author_id')->unsigned()->nullable();
            $table->foreign('author_id')->references('id')->on('users');
        });
        foreach (Course::all() as $course) {
            $course->author_id = $course->author()->id;
            $course->save();
        }
    }
}
