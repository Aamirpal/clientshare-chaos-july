<?php

namespace App\Jobs;
use Illuminate\Bus\Queueable;
// use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;  
use App\Helpers\Aws;
use App\Http\Controllers\MailerController;
use App\Space;
use ZipArchive;
use Mail;
use App\Helpers\Analytic;


class MakeAndSendAnalyticsReport implements ShouldQueue
{
    use InteractsWithQueue, Queueable;
    // SerializesModels;

protected $job_data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($job_data)
    {
        $this->job_data = $job_data; 
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(){
        ini_set('memory_limit','-1');
            if(sizeOfCustom($this->job_data)){ 
                $zip = new ZipArchive;    
                $zip_file = tempnam('.', '');   
                $zip->open($zip_file, ZipArchive::CREATE);     
                foreach ($this->job_data['spaces'] as $key => $space) {   
                    $file = (new Analytic)->triggerReport($space['id'],date('m'),date('Y'));      
                    $zip->addFromString(removeSpecialCharacters($space['share_name']).'.xlsx',$file->string('xlsx'));   
                }   
                $zip->close(); 
                $file_path = Space::uploadSpacesAnalyticsReport($zip_file,$this->job_data['logged_in_user']['id'].'/'.rand().'/Client Share Analytics Data');
                if(!empty($file_path)){
                    $email_data = 
                    [ 'file_path' => $file_path,
                        'user_name' => $this->job_data['logged_in_user']['first_name'],
                        'user_email' => $this->job_data['logged_in_user']['email']
                    ];
                }
                $this->sendEmail($email_data);
            }
        $this->delete();
        return true;
    }
    

     private function sendEmail($mail_data) {
        if (!empty($mail_data)) {
            $domainName = env('APP_URL');
            $data['email_to'] = $mail_data['user_email'];
            $data['file_link'] = $mail_data['file_path'];
            $data['subject'] = 'Client Share Analytics Data';
            $data['path'] = $domainName; 
            $data['user_name'] = $mail_data['user_name'];
            Mail::send('email.export_analytics_report', ['data' => $data], function ($message) use($data) {
                $message->from(env("SENDER_FROM_EMAIL"), env("SENDER_NAME"));
                $message->to($data['email_to']);
                $message->subject($data['subject']);
                $message->replyTo($data['email_to'] ?? env("SENDER_FROM_EMAIL"));
            });
            return response()->json(['message' => 'Request completed']);
        }
        //return view('email.export_analytics_report',$mail_data);
     }
}