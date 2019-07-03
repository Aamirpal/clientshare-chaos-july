<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeGroupsIdDataType extends Migration
{

    public function up()
    {
        Schema::table('groups', function(Blueprint $table){
            $table->increments('id')->change();
        });    
    }

    public function down()
    {
        Schema::table('groups', function(Blueprint $table){
            $table->bigIncrements('id')->change();
        });
    }
}
