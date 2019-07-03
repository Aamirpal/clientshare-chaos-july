<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailHeaderToSpaces extends Migration
{
    public function up(){
        Schema::table('spaces', function($table){
            $table->json('email_header')->nullable();
        });
    }

    public function down(){
        Schema::table('spaces', function($table){
            $table->dropColumn('email_header');
        });
    }
}
