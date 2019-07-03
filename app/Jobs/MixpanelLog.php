<?php

namespace App\Jobs;

use App\Helpers\Logger;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class MixpanelLog implements ShouldQueue {
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $log_data;
    public function __construct($log_data) {
        $this->log_data = $log_data;
    }

    public function handle() {
        return (new Logger)->mixPannelLog($this->log_data);
    }
}
