<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotificationRelatedFieldsToCourseUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_user', function (Blueprint $table) {
            $table->boolean('course_completed_notification')->default(false);
            $table->boolean('deadline_notification')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_user', function (Blueprint $table) {
            $table->dropColumn([
                'course_completed_notification',
                'deadline_notification',
            ]);
        });
    }
}
