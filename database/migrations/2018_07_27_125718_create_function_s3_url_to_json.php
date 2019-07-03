<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFunctionS3UrlToJson extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::unprepared("CREATE OR REPLACE FUNCTION s3_url_to_json(path text, bucket text)
                      RETURNS json AS
                    $$
                    DECLARE
                        url character varying;
                        fname text;
                        dim integer;
                    BEGIN
                        
                    IF path IS NULL OR bucket = '' THEN
                        RETURN NULL;
                    END IF;
                    IF position('/' in path) = 0 THEN
                        RETURN json_build_object('path', '', 'file', path);
                    END IF;
                        bucket = bucket || '/';
                        url = url_decode(split_part(path, bucket, 2));
                        dim = cardinality(string_to_array(url, '/'));
                    IF dim = 0 OR position(bucket in path) = 0 THEN
                        RETURN NULL;
                    END IF;
                        fname = split_part(url, '/', dim);

                        RETURN json_build_object('path', array_remove(string_to_array(url, '/'), fname), 'file', fname);
                        
                    END;$$
                      LANGUAGE plpgsql IMMUTABLE;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::unprepared("DROP FUNCTION \"s3_url_to_json\"(path text, bucket text)");
    }
}
