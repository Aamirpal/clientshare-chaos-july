<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateShareSetupStepsMigration extends Migration
{
   public function up()
    {
        DB::statement
        (
            "UPDATE spaces SET share_setup_steps = 10"
        );
    }

    public function down()
    {
        DB::statement
        (
            "UPDATE spaces SET share_setup_steps = 0"
        );
    }
}
