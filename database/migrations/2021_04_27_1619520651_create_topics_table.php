<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopicsTable extends Migration
{
    public function up()
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('title')->nullable();
            $table->bigInteger('lesson_id', )->unsigned();
            $table->bigInteger('topicable_id')->nullable();
            $table->string('topicable_class')->nullable();
            $table->integer('order')->unsigned()->default(0);
            $table->foreign('lesson_id')->references('id')->on('lessons');
        });
    }

    public function down()
    {
        Schema::dropIfExists('topics');
    }
}
