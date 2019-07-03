<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Controllers\MailerController;

class SendCampaignNotification implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $user_id, $mail_data, $view, $share_id;
    public function __construct($user_id, $share_id, $view, $mail_data) {
        $this->user_id = $user_id;
        $this->share_id = $share_id;
        $this->view = $view;
        $this->mail_data = $mail_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        return (new MailerController)->userInvitation($this->user_id, $this->share_id, $this->view, $this->mail_data);
    }
}
