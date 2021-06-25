<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTopicTable extends Migration
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
            $table->dropColumn('active')->default(true);
            $table->dropColumn('subtitle', 255)->nullable();
            $table->dropColumn('language', 2)->nullable();
            $table->dropColumn('description')->nullable();
            $table->dropColumn('level')->nullable();
        });
    }
}
