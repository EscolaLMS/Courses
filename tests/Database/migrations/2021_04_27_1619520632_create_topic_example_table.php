<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopicExampleTable extends Migration
{
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

    public function down()
    {
        Schema::dropIfExists('topic_example');
        Schema::dropIfExists('topic_second_example');
    }
}
