<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopicExampleTest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('topic_example', function (Blueprint $table) {
            $table->string('value', 255);
            $table->timestamps();
            $table->bigIncrements('id');
        });
        Schema::create('topic_second_example', function (Blueprint $table) {
            $table->string('value', 255);
            $table->timestamps();
            $table->bigIncrements('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('topic_example');
        Schema::dropIfExists('topic_second_example');
    }
}
