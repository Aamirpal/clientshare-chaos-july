<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateColumnS3FilePathFromPostFileUrlInPostMedia extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("UPDATE post_media SET s3_file_path = (SELECT s3_url_to_json(post_file_url, 'clientshare-docs') FROM post_media AS media WHERE media.id = post_media.id)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("UPDATE post_media SET s3_file_path = NULL");
    }
}
