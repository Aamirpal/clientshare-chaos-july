<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateLogosColumnsValueSpaceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("UPDATE spaces SET
    seller_logo = (SELECT s3_url_to_json(company_seller_logo, 'clientshare-docs') FROM spaces AS sps WHERE sps.id = spaces.id and company_seller_logo ilike '%clientshare-docs%'),
    buyer_logo = (SELECT s3_url_to_json(company_buyer_logo, 'clientshare-docs') FROM spaces AS sps WHERE sps.id = spaces.id and company_buyer_logo ilike '%clientshare-docs%'),
    processed_seller_logo = (SELECT s3_url_to_json(seller_processed_logo, 'clientshare-docs') FROM spaces AS sps WHERE sps.id = spaces.id and seller_processed_logo ilike '%clientshare-docs%'),
    processed_buyer_logo = (SELECT s3_url_to_json(buyer_processed_logo, 'clientshare-docs') FROM spaces AS sps WHERE sps.id = spaces.id and buyer_processed_logo ilike '%clientshare-docs%')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("UPDATE spaces SET seller_logo = NULL, buyer_logo = NULL, processed_seller_logo = NULL, processed_buyer_logo = NULL");
    }
}
