<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampsToCourseGroupPivotTable extends Migration
{
    public function up(): void
    {
        Schema::table('course_group', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('course_group', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
}
