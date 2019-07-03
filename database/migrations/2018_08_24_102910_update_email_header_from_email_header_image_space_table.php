<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateEmailHeaderFromEmailHeaderImageSpaceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("UPDATE spaces SET email_header = (SELECT s3_url_to_json(email_header_image, 'clientshare-docs') FROM spaces AS sp WHERE sp.id = spaces.id)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("UPDATE spaces SET email_header = NULL");
    }
}
