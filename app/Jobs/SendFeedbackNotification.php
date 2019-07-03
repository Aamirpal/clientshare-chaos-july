<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendFeedbackNotification implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    Protected $space_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($space_id) {
        $this->space_id = $space_id;        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        return (new \App\Http\Controllers\PostController)->triggerFeedback($this->space_id);
    }
}
