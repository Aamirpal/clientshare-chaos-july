<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LogoColumnsForSpacesTable extends Migration{
    
    public function up(){
        Schema::table('spaces', function($table){
            $table->json('seller_logo')->nullable();
            $table->json('buyer_logo')->nullable();
            $table->json('processed_seller_logo')->nullable();
            $table->json('processed_buyer_logo')->nullable();
        });
    }

    public function down(){
        Schema::table('spaces', function($table){
            $table->dropColumn('seller_logo');
            $table->dropColumn('buyer_logo');
            $table->dropColumn('processed_seller_logo');
            $table->dropColumn('processed_buyer_logo');
        });
    }
}
