<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveFileUrlColumnFromCommentAttachment extends Migration
{
    public function up()
    {
        Schema::table('comment_attachments', function (Blueprint $table) {
            $table->renameColumn('file_url', 'file_url_old')->nullable();
            $table->string('file_url')->nullable()->change();

        });
    }

    public function down()
    {
        Schema::table('comment_attachments', function (Blueprint $table) {
            $table->renameColumn('file_url_old', 'file_url');
            $table->string('file_url_old')->change();
        });
    }
}
