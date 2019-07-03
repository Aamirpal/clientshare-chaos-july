<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveCloudFilesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('remove_cloud_files')) {
            return;
        }
    
        Schema::create('remove_cloud_files', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tag')->default('s3');
            $table->string('file_url');
            $table->json('file_cloud_path');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('remove_cloud_files');
    }
}