<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Controllers\ManageShareController;
use App\Http\Controllers\MailerController;
use Config;
use App\Space;
use Storage;

class MoveClearBitLogo implements ShouldQueue {
    use InteractsWithQueue, Queueable;

    Protected $shares, $columns;

    public function __construct($shares, $columns) {
        $this->shares = $shares;
        $this->columns = $columns;
    }

    public function handle() {
        foreach ($this->shares as $share) {
            try{
                $file_data = [
                    'folder' => '/company_logo/',
                    'file_name' => time().'.png',
                    's3_url' => config('constants.s3.url'),
                    'file_content' => file_get_contents($share[$this->columns['wrapper_column']])
                ];
                uploadFileOnS3($file_data);
                $data = [
                    $this->columns['new_column'] => json_encode([
                        'path' => ['company_logo'],
                        'file' => $file_data['file_name']
                    ])
                ];
            } catch(\Exception $e){
                $data = [$this->columns['old_column'] => ''];
            }            
            (new Space)->UpdateClearBitLogo($share['id'], $data);
        }
    }
}