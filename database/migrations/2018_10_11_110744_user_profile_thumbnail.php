<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserProfileThumbnail extends Migration
{
    public function up()
    {
        Schema::table('users', function(Blueprint $table){
            $table->json('profile_thumbnail')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function(Blueprint $table){
            $table->dropColumn('profile_thumbnail');
        });
    }
}
