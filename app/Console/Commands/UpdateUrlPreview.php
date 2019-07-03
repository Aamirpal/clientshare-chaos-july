<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateUrlPreview extends Command
{
    protected $signature = 'command:populateUrlPreview';
    protected $description = 'Command to populate posts url-preview from V1 to V2';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        return \DB::select("
            UPDATE posts set url_preview = metadata->'get_url_data' where 
            metadata->'get_url_data' is not null and url_preview is null
        ");
    }
}
