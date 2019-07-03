 <?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCommentAttachments extends Migration {
    public function up() {
        Schema::create('comment_attachments', function(Blueprint $table){
            $table->uuid('id')->default(DB::raw('uuid_generate_v1()'));
            $table->uuid('comment_id');
            $table->string('file_url');
            $table->json('metadata')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('file_name')->nullable();
            $table->timestamps();

            $table->index('comment_id');
            $table->index('file_name');
            $table->index('mime_type');

            $table->primary('id');
            $table->foreign('comment_id')->references('id')
                ->on('comments')->onDelete('cascade');

        });
    }

    public function down() {
        Schema::table('comment_attachments', function (Blueprint $table) {
            $table->dropIndex('comment_attachments_comment_id_index');
            $table->dropIndex('comment_attachments_file_name_index');
            $table->dropIndex('comment_attachments_mime_type_index');

        });
        Schema::drop('comment_attachments');
    }
}
