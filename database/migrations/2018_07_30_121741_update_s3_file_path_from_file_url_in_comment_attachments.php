<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateS3FilePathFromFileUrlInCommentAttachments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("UPDATE comment_attachments SET s3_file_path = (SELECT s3_url_to_json(file_url, 'clientshare-docs') FROM comment_attachments AS cmt_media WHERE cmt_media.id = comment_attachments.id)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("UPDATE comment_attachments SET s3_file_path = NULL");
    }
}
