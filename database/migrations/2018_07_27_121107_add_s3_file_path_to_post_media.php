<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddS3FilePathToPostMedia extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('post_media', function (Blueprint $table) {
            //
            $table->json('s3_file_path')->after('post_file_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('post_media', function (Blueprint $table) {
            //
            $table->dropColumn('s3_file_path');
        });
    }
}
