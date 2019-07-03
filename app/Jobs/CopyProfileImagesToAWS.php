<?php

namespace App\Jobs;

use App\Helpers\Aws;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CopyProfileImagesToAWS implements ShouldQueue {
    use InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle() {
        return (new Aws)->copyAllProfileImagesToAWS();
    }
}
