<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopicH5PsTable extends Migration
{
    public function up()
    {
        Schema::create('topic_h5ps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('value');
        });
    }

    public function down()
    {
        Schema::dropIfExists('topic_h5ps');
    }
}
