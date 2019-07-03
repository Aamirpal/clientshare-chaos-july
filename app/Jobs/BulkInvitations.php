<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Controllers\ManageShareController;
use App\Http\Controllers\MailerController;
use Config;

class BulkInvitations implements ShouldQueue {
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    Protected $mail_data, $session_space_info, $user_invite;

    public function __construct($mail_data, $session_space_info, $user_invite = null) {
        $this->mail_data = $mail_data;
        $this->user_invite = $user_invite;
        $this->session_space_info = $session_space_info;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $count=0;
        foreach ($this->mail_data['users'] as $key => $user) {
            if(sizeOfCustom($user['status'])){ 
                continue;
            } else {
                $count++;
            }

            $mail_data['share_id'] = $this->mail_data['share_id'];
            $mail_data['user'] = $user;
            $mail_data['user']['subject'] = $this->mail_data['mail']['subject'];
            $mail_data['mail'] = $this->mail_data['mail'];
            $mail_data['mail']['to'] = $user['email'];
            $mail_data['call_via_bulk_invitation'] = true;
            $mail_data['mail_headers'] = [
                'X-PM-Tag' => 'bulk-users-invitation',
                'space_id' => $this->mail_data['share_id']
            ];
            (new ManageShareController)->processInvitation($mail_data, $this->session_space_info, $this->user_invite);
        }
        if($this->user_invite != Config::get('constants.INVITE_EXPORT')){
          (new MailerController)->bulkInvitationReport(['session_space_info' =>$this->session_space_info, 'total_requested'=>sizeOfCustom($this->mail_data['users']), 'total_send'=>$count, 'sender_user'=>$this->session_space_info['sender_user']]);
        }
        return true;
    }
}