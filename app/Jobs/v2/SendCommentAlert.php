<?php

namespace App\Jobs\v2;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Traits\v2\Comment as CommentTrait;

class SendCommentAlert implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, CommentTrait;
    
    protected $alert_data, $group_users;

    public function __construct($alert_data, $group_users)
    {
        $this->alert_data = $alert_data;
        $this->group_users = $group_users;
    }

    public function handle()
    {
        return $this->sendAlerts($this->alert_data['comment'], $this->alert_data['current_user']);
    }
}
