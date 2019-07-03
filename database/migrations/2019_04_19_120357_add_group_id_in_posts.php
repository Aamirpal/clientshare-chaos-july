<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGroupIdInPosts extends Migration
{
    
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->integer('group_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('group_id');
        });
    }
}
