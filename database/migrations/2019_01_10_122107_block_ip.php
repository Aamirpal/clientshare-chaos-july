<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BlockIp extends Migration
{

    public function up()
    {
        Schema::table('spaces', function(Blueprint $table){
            $table->json('allowed_ip')->nullable();
            $table->boolean('ip_restriction')->default(false);
        });
    }

    public function down()
    {
        Schema::table('spaces', function(Blueprint $table){
            $table->dropColumn('allowed_ip');
            $table->dropColumn('ip_restriction');
        });   
    }
}
