<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopicRichtextsTable extends Migration
{
    public function up()
    {
        Schema::create('topic_richtexts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->bigInteger('topic_id')->unsigned();
            $table->mediumText('value')->default('');
            $table->foreign('topic_id')->references('id')->on('topics');
        });
    }

    public function down()
    {
        Schema::dropIfExists('topic_richtexts');
    }
}
