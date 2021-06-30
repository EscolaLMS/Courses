<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('active')->default(true);
            $table->string('subtitle', 255)->nullable();
            $table->string('language', 2)->nullable();
            $table->text('description')->nullable();
            $table->string('level', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('active');
            $table->dropColumn('subtitle');
            $table->dropColumn('language');
            $table->dropColumn('description');
            $table->dropColumn('level');
        });
    }
}
