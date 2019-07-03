<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBusinessReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('user_id');
            $table->uuid('space_id');
            $table->string('title');
            $table->text('description');
            $table->dateTime('review_date');
            $table->integer('group_id');
            $table->integer('conducted_via');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('space_id')->references('id')->on('spaces');
            $table->foreign('group_id')->references('id')->on('groups');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_reviews');
    }
}
