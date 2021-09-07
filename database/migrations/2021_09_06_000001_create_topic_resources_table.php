<?php

use EscolaLms\Courses\Models\Topic;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopicResourcesTable extends Migration
{
    public function up()
    {
        Schema::create('topic_resources', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->foreignIdFor(Topic::class);
            $table->string('path', 255);
            $table->string('name', 255);
        });
    }

    public function down()
    {
        Schema::dropIfExists('topic_resources');
    }
}
