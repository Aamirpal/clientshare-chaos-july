<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Traits\Comment as CommentTrait;

class SendCommentAlert implements ShouldQueue {
    use InteractsWithQueue, Queueable, SerializesModels, CommentTrait;
    
    protected $alert_data;
    public function __construct($alert_data) {
        $this->alert_data = $alert_data;
    }

    public function handle() {
        return $this->sendAlerts($this->alert_data['comment'], $this->alert_data['current_user']);
    }
}
