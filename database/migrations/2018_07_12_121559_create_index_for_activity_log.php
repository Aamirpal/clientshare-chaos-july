<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndexForActivityLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('activity_logs', function(Blueprint $table){
            $table->index('space_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activity_logs', function(Blueprint $table){
            $table->dropIndex('activity_logs_space_id_index');
            $table->dropIndex('activity_logs_created_at_index');
        });
    }
}
