<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IndexesForNotificationModule extends Migration {
    public function up() {
        Schema::table('space_users', function(Blueprint $table){
            $table->index(['user_id', 'space_id']);
            $table->index('space_id');
            $table->index('user_status');
        });

        Schema::table('notifications', function(Blueprint $table){
            $table->index('user_id');
            $table->index('space_id');
        });
    }
    
    public function down() {
        Schema::table('space_users', function(Blueprint $table){
            $table->dropIndex('space_users_user_id_space_id_index');
            $table->dropIndex('space_users_space_id_index');
            $table->index('space_users_user_status_index');
        });

        Schema::table('notifications', function(Blueprint $table){
            $table->dropIndex('notifications_user_id_index');
            $table->dropIndex('notifications_space_id_index');
        });
    }
}