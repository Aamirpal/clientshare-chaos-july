<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShareSetupStepsAddInSpaceUser extends Migration
{
    public function up()
    {
        Schema::table('spaces', function(Blueprint $table){
            $table->tinyInteger('share_setup_steps')->default(0);
        });
    }

    public function down()
    {
        Schema::table('spaces', function(Blueprint $table){
            $table->dropColumn('share_setup_steps');
        });
    }
}
