<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopicPdfsTable extends Migration
{
    public function up()
    {
        Schema::create('topic_pdfs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('value', 255);
        });
    }

    public function down()
    {
        Schema::dropIfExists('topic_pdfs');
    }
}
