<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class JointShareLogos extends Migration
{
    
    public function up()
    {
         Schema::table('spaces', function(Blueprint $table){
            $table->json('joint_share_email_logos')->nullable();
        });
    }

    public function down()
    {
        Schema::table('spaces', function(Blueprint $table){
            $table->dropColumn('joint_share_email_logos');
        });
    }
}
