<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateS3FilePathInUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        //
        DB::statement("UPDATE users SET profile_image = (SELECT s3_url_to_json(profile_image_url_old, 'clientshare-docs') FROM users AS usr WHERE usr.id = users.id)");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    { 
        //
        DB::statement("UPDATE users SET profile_image = NULL");

    }
}
