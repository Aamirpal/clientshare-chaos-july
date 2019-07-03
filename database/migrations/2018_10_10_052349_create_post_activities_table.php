<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostActivitiesTable extends Migration
{
    public function up()
    {
        Schema::create('post_activities', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('post_id');
            $table->uuid('user_id');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('post_activities');
    }
}
