<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndexForQuickLinksSpaceId extends Migration
{
    public function up()
    {
        Schema::table('quick_links', function(Blueprint $table){
            $table->index('share_id');
        });
    }
    public function down()
    {
        Schema::table('quick_links', function(Blueprint $table){
            $table->dropIndex('quick_links_share_id_index');
        });
    }
}
