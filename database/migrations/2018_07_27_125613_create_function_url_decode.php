<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFunctionUrlDecode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::unprepared("CREATE OR REPLACE FUNCTION url_decode(input text) RETURNS text
                    LANGUAGE plpgsql IMMUTABLE STRICT AS $$
                    DECLARE
                     bin bytea = '';
                     byte text;
                    BEGIN
                     FOR byte IN (select (regexp_matches(input, '(%..|.)', 'g'))[1]) LOOP
                       IF length(byte) = 3 THEN
                         bin = bin || decode(substring(byte, 2, 2), 'hex');
                       ELSE
                         bin = bin || byte::bytea;
                       END IF;
                     END LOOP;
                     RETURN convert_from(bin, 'utf8');
                    END
                    $$;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::unprepared("DROP FUNCTION \"url_decode\"(input text)");
    }
}
