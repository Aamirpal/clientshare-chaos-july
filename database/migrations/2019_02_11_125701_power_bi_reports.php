<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PowerBiReports extends Migration
{
    
    public function up()
    {
        Schema::create('power_bi_reports', function(Blueprint $table){
            $table->increments('id');
            $table->uuid('space_id');
            $table->uuid('user_id');
            $table->string('report_type');
            $table->string('report_name');
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('power_bi_reports', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('space_id')->references('id')->on('spaces');
        });
    }

    public function down()
    {
        Schema::table('power_bi_reports', function (Blueprint $table) {
            $table->dropForeign('power_bi_reports_user_id_foreign');
            $table->dropForeign('power_bi_reports_space_id_foreign');
        });

        Schema::drop('power_bi_reports');
    }
}
