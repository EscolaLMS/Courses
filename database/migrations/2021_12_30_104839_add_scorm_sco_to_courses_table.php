<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScormScoToCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign('courses_scorm_id_foreign');
            $table->dropColumn('scorm_id');

            $table->bigInteger('scorm_sco_id')->unsigned()->nullable();
            $table->foreign('scorm_sco_id')->references('id')->on('scorm_sco');
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
            $table->bigInteger('scorm_id')->unsigned()->nullable();
            $table->foreign('scorm_id')->references('id')->on('scorm');

            $table->dropColumn('scorm_sco_id');
        });
    }
}
