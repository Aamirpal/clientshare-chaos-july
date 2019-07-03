<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Invitation;
use App\User;
use App\Helpers\bulkInvitation;
use App\SpaceUser;
use Storage;
use App\Space;
use Config;
use Mail;
use App\Http\Controllers\ManageShareController;
use Excel;

class BulkInvitationUrls implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */    
    Protected $invite_users;
    public function __construct($invite_users) {
        $this->invite_users = $invite_users;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {  
        if(!sizeOfCustom($this->invite_users)) return false;           
        $this->invite_users['share'] = Space::find($this->invite_users['share_id'])->makeVisible(['company_seller_logo', 'company_buyer_logo', 'seller_processed_logo', 'buyer_processed_logo'])->toArray();
        $extension = 'xlsx';
        $s3 = Storage::disk('s3');
        $s3_bucket = env("S3_BUCKET_NAME");
        $file_path = '/invitation/'.$this->invite_users['user']->id.'/Bulk_CSV_URLs'.strtotime("now").'.'.$extension;
        $full_url = $file_path;
        $file = (new bulkInvitation)->bulkInvitationUrl($this->invite_users);              
        if( $s3->put($file_path, $file->string($extension), 'public') ){
            $this->invite_users['file_path'] = $full_url;
            return $this->sendReport($this->invite_users);
        }
        return false;   
    }

    function sendReport($mail_data) {
        $mail_data['path'] = env('APP_URL');
        Mail::send('email.bulk_invitation', ['mail_data' => $mail_data], function ($message) use($mail_data) {
            $message->from(env('SENDER_FROM_EMAIL'), env('SENDER_NAME'));
            $message->to( $mail_data['user']->email );
            $message->subject( 'Bulk Invitation' );
            $message->replyTo( env('SENDER_FROM_EMAIL') );
        });
        return true;
    }
}
