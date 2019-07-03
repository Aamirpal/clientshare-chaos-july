<?php

namespace App\Jobs;

use App\Http\Controllers\ManageShareController;
use Illuminate\Bus\Queueable;
// use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Image;
use App\Space;  
use App\Helpers\Aws;
use App\Http\Controllers\MailerController;

class ConvertRoundImage implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

protected $job_data;

    public function __construct($job_data)
    {
        $this->job_data = $job_data;
    }

    public function handle() {
        ini_set('memory_limit','-1');
        $space = Space::find($this->job_data['share_id']);
        if(sizeOfCustom($space)){ 
            if(empty($this->job_data['buyer_logo'])){
                $this->job_data['buyer_logo'] = env('APP_URL').'/images/login_user_icon.png';
            }
            if(empty($this->job_data['seller_logo'])){
                $this->job_data['seller_logo'] = env('APP_URL').'/images/login_user_icon.png';
            }
            if (is_array($this->job_data['buyer_logo']))
                $space->setBuyerLogo(getAwsSignedURL(composeUrl($this->job_data['buyer_logo'])), time().'_'.rand());

            if (is_array($this->job_data['seller_logo']))
                $space->setSellerLogo(getAwsSignedURL(composeUrl($this->job_data['seller_logo'])), time().'_'.rand());
        }

        if($this->job_data['form_edit'] == 'false'){ 
           //
            $this->job_data['share_data']['buyer_processed_logo'] = $space['buyer_processed_logo'];
            $this->job_data['share_data']['seller_processed_logo'] = $space['seller_processed_logo'];
          
           // // send email code
           (new MailerController)->spaceInvitation($this->job_data['user_id'], $this->job_data['share_id'],$this->job_data['share_data']);
       }
      $this->delete();
       return true;
    }
}
