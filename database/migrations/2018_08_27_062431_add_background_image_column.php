<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBackgroundImageColumn extends Migration
{
    public function up()
    {
         Schema::table('spaces', function($table){
            $table->json('background_image')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('spaces', function($table){
            $table->dropColumn('background_image');
        });
    }
}
