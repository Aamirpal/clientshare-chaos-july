<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShareSetupStepsColumn extends Migration
{
    
    public function up()
    {
        Schema::table('users', function(Blueprint $table){
            $table->integer('share_setup_steps')->default(0);
        });

        DB::statement
        (
            "UPDATE users SET share_setup_steps = 7"
        );
    }

    public function down()
    {
        Schema::table('users', function(Blueprint $table){
            $table->dropColumn('share_setup_steps');
        });
    }
}
