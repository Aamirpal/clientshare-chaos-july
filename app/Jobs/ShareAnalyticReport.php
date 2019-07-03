<?php

namespace App\Jobs;

use Config;
use Mail;
use Storage;
use ZipArchive;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Helpers\Analytic;
use App\Space;

class ShareAnalyticReport implements ShouldQueue {
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $job_data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($job_data) {
        $this->job_data = $job_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        ini_set('memory_limit','-1');
        if(!sizeOfCustom($this->job_data)) return false;
            
        $s3 = Storage::disk('s3');
        $extension = 'xlsx';
        $file_name = removeSpecialCharacters($this->job_data['share']['share_name']). '.' .$extension;
        $s3_bucket = env("S3_BUCKET_NAME");
        $file = (new Analytic)->triggerReport($this->job_data['report_data']['share_id'],$this->job_data['report_data']['month'],$this->job_data['report_data']['year']);
        $zip = new ZipArchive;
        $zip_file = tempnam('.', '');
        $zip->open($zip_file, ZipArchive::CREATE);
        $zip->addFromString(removeSpecialCharacters($this->job_data['share']['share_name']).'.xlsx',$file->string('xlsx'));   
        $zip->close();
        $full_url = Space::uploadSpacesAnalyticsReport($zip_file,$this->job_data['user']['id'].'/'.rand().'/Client Share Analytics Data');
        
        $this->job_data['file_path'] = $full_url;
        return $this->sendReport($this->job_data);
    }

    /**/
    function sendReport($mail_data) {
        $mail_data['path'] = env('APP_URL');
        Mail::send('email.share_analytic_report', ['mail_data' => $mail_data], function ($message) use($mail_data) {
            $message->from(env('SENDER_FROM_EMAIL'), env('SENDER_NAME'));
            $message->to( $mail_data['user']['email'] );
            $message->subject( $mail_data['mail']['subject'] );
            $message->replyTo( env('SENDER_FROM_EMAIL') );
        });
        return true;
    }
}
