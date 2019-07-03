<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBackgroundImageColumnMigration extends Migration
{
    
    public function up()
    {
        DB::statement("UPDATE spaces SET
        background_image = (SELECT s3_url_to_json(background_logo, 'clientshare-docs') FROM spaces AS sps WHERE sps.id = spaces.id and background_logo ilike '%clientshare-docs%')");
    }

    public function down()
    {
         DB::statement("UPDATE spaces SET background_image = NULL");
    }
}
