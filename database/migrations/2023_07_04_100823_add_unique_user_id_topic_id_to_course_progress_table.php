<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueUserIdTopicIdToCourseProgressTable extends Migration
{
    public function up(): void
    {
        Schema::table('course_progress', function (Blueprint $table) {
            $table->unique(['user_id', 'topic_id']);
        });
    }

    public function down(): void
    {
        Schema::table('course_progress', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'topic_id']);
        });
    }
}
