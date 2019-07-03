<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateS3FilePathFromMediaPathInMedia extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("UPDATE media SET s3_file_path = (SELECT s3_url_to_json(media_path, 'clientshare-docs') FROM media AS ex_media WHERE ex_media.id = media.id)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("UPDATE media SET s3_file_path = NULL");
    }
}
