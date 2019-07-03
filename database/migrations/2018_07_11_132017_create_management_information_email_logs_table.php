<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManagementInformationEmailLogsTable extends Migration{

    public function up(){
        Schema::create('management_information_email_logs', function(Blueprint $table){
            $table->increments('id');
            $table->uuid('space_id');
            $table->json('metadata');
            $table->timestamps();

            $table->index('space_id');

            $table->foreign('space_id')->references('id')
                ->on('spaces')->onDelete('cascade');

        });
    }

    public function down(){
        Schema::table('management_information_email_logs', function (Blueprint $table) {
            $table->dropIndex('management_information_email_logs_space_id_index');
        });
        Schema::drop('management_information_email_logs');
    }
}
