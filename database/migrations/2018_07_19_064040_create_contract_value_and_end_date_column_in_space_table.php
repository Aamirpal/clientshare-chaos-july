<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractValueAndEndDateColumnInSpaceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('spaces', function($table){
            $table->double('contract_value')->nullable();
            $table->date('contract_end_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spaces', function($table)
        {
            $table->dropColumn('contract_value');
            $table->dropColumn('contract_end_date');
        });
    }
}
