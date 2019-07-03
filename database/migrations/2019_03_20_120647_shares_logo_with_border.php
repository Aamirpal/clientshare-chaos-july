<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SharesLogoWithBorder extends Migration
{
    public function up()
    {
         Schema::table('spaces', function(Blueprint $table){
            $table->json('seller_circular_logo')->nullable();
            $table->json('buyer_circular_logo')->nullable();
        });
    }

    public function down()
    {
        Schema::table('spaces', function(Blueprint $table){
            $table->dropColumn('seller_circular_logo');
            $table->dropColumn('buyer_circular_logo');
        });
    }
}
