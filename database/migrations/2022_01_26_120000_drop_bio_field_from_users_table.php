<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropBioFieldFromUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('bio');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('bio')->nullable();
        });
    }
}
