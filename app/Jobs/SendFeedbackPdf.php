<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use mikehaertl\wkhtmlto\Pdf as PDF;
use Mail;

class SendFeedbackPdf implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

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
    public function handle()
    {
        if(sizeOfCustom($this->job_data)){
            $file = 'The_'.$this->job_data['month'].'_feedback_for_the_'.$this->job_data['space_name'].'_Client_Share_'.rand().'.pdf';
            
            $path = \h4cc\WKHTMLToPDF\WKHTMLToPDF::PATH;
            $pdf = new PDF(array('binary'=>$path));
            
            $pdf->addPage($this->job_data['content']);
            $temp_pdf = $pdf->getTmpDir().'/'.rand().'pdf';
            $temp_file = fopen($temp_pdf, 'w');
            fclose($temp_file);
            $pdf->saveAs($temp_pdf);
            $file_path = $this->uploadFeedbackPdf($temp_pdf, $file);
            if(!empty($file_path)){
                $email_data = 
                [ 'file_path' => $file_path,
                    'user_name' => $this->job_data['logged_in_user']['first_name'],
                    'user_email' => $this->job_data['logged_in_user']['email'],
                    'space_name' => $this->job_data['space_name'],
                    'company_seller_logo' => $this->job_data['company_seller_logo'],
                    'company_buyer_logo' => $this->job_data['company_buyer_logo'],
                    'month' => $this->job_data['month']
                ];
            }
            $this->sendEmail($email_data);
        }
        
        return true;
    }

    private function sendEmail($mail_data) {
        if (!empty($mail_data)) {
            $domainName = env('APP_URL');
            $data = $mail_data;
            $data['email_to'] = $mail_data['user_email'];
            $data['file_link'] = $mail_data['file_path'];
            $data['subject'] = 'Client Share Feedback PDF';
            $data['path'] = $domainName; 
            Mail::send('email.feedback_pdf_link', ['data' => $data], function ($message) use($data) {
                $message->from(env("SENDER_FROM_EMAIL"), env("SENDER_NAME"));
                $message->to($data['email_to']);
                $message->subject($data['subject']);
                $message->replyTo($data['email_to'] ?? env("SENDER_FROM_EMAIL"));
            });
            return response()->json(['message' => 'Request completed']);
        }
        //return view('email.feedback_pdf_link',$mail_data);
     }

  private function uploadFeedbackPdf($file,$name){
        if(!empty($file) && !empty($name)){
            $s3 = \Storage::disk('s3');
            $s3_bucket = env("S3_BUCKET_NAME");
            $filePath = '/feedback_pdf/' . $name;
            $fulleurl1 = "https://s3-eu-west-1.amazonaws.com/".$s3_bucket."".$filePath;
            $s3->put($filePath, file_get_contents($file), 'public');
            return $fulleurl1;
        }
    }
}
