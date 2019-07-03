<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use App\Post;
use App\PostMedia;
use Session;
use Redirect;
use Auth;
use App\Comment;
use App\Notification;
use DB;
use Carbon\Carbon;
use Storage;
use Hash;
use App\SpaceUser;
use Validator;
use App\User;
use App\Media;
use App\Space;
use App\Company;
use App\Feedback;
use App\Helpers\Aws;
use App\Helpers\Logger;
use App\Http\Controllers\{MailerController,FeedbackController};
use Illuminate\Support\Facades\View;
use mikehaertl\wkhtmlto\Pdf as PDF;

class FeedbackPdfController extends Controller
{

    public function feedbackPdf(Request $request, $share_id='',$month='',$year='') {
       $send_email = false;
       if(!empty($request->all()['send_email']))
          $send_email = $request->all()['send_email'];   

       $month_number = $month;       
       $month_name = date('F', mktime(0, 0, 0, $month_number, 10));
       $share_name = Session::get('space_info')['share_name']; 
       $file='The_'.$month_name.'_feedback_for_the_'.$share_name.'_Client_Share.pdf';
       $space_id = Session::get('space_info')['id']; 
       $user_id = Auth::user()->id;    
       if($month !=''){ $month = $month; }else{  $month = date('m'); } 
       if($year !=''){ $year = $year; }else{   $year = date('Y'); }     
       $feedback = (new Feedback)->userFeedback($space_id,$year,$month);
        
       $very_good = $good = $bad = $current_user = $all_user = array();
       $nps = '';
       if(!empty($feedback))
       {             
         foreach ($feedback as $val) 
         {
            if($val->user_id == $user_id)          
              array_push($current_user, $val);
            else            
              array_push($all_user, $val);

          /*Calculation for NPS*/
            if($val->rating > 8)
               $very_good[] = $val->rating;
            elseif($val->rating > 6 && $val->rating < 9)
               $good[] = $val->rating;
            else
               $bad[] = $val->rating;
          }
        $feedback = array_merge($current_user,$all_user);
        $very_good  = sizeOfCustom($very_good); 
        $good   = sizeOfCustom($good);
        $bad    = sizeOfCustom($bad);
        $total  = $very_good + $good + $bad;
        $average_good_response = ($very_good/$total)*100;
        $average_bad_response = ($bad/$total)*100;
        $nps = $average_good_response - $average_bad_response;
        $nps = round($nps);
        /*End Calculation of NPS*/
      }
      $get_non_feedback_user = (new SpaceUser)->getNonFeedbackUser($space_id, $year, $month);
      /* Log "visit feedback" event */
      (new Logger)->log([
        'action' => 'download feedback pdf',
        'description' => 'download feedback pdf'
      ]);
      $give_feedback = (new FeedbackController)->giveFeed($space_id,$user_id);
      $check_buyer = $this->checkBuyer($space_id,$user_id);

      $data['current_quater'] = Carbon::parse($month.'/01/'.$year)->subMonth(3)->format('F Y').' - '.Carbon::parse($month.'/01/'.$year)->subMonth(1)->format('F Y');
      
      $view = View::make('pdf_feedback',['data'=>$data, 'feedback'=>$feedback,
        'nps'=>$nps,'month'=> $month,
        'get_non_feedback_user' => $get_non_feedback_user,
        "give_feedback"=>$give_feedback,
        'check_buyer'=>$check_buyer]
      );
        
      $contents = $view->render();
      if($send_email){
        $job_data = ['logged_in_user' => Auth::user(),
                      'content' => $contents,
                      'space_name' => $share_name,
                      'company_seller_logo' => Session::get('space_info')['company_seller_logo'],
                      'company_buyer_logo' => Session::get('space_info')['company_buyer_logo'],
                      'month' => $month_name
                      ];

        dispatch(new \App\Jobs\SendFeedbackPdf($job_data));
        return ;
      }
      $path = \h4cc\WKHTMLToPDF\WKHTMLToPDF::PATH;
      $pdf = new PDF(array('binary'=>$path));
      
      $pdf->addPage($contents);
      $pdf->send($file);
         
    }


    function checkBuyer($space_id,$user_id){
        $checkBuyer = DB::table('space_users as su')
                   ->select('s.company_seller_id','s.company_buyer_id','su.metadata->user_profile->company as company_id')
                   ->join('spaces as s','su.space_id','s.id')
                   ->where('su.space_id', '=', $space_id)
                   ->where('su.user_id', '=', $user_id)
                   ->get()->first(); 
        $buyer_id =  isset($checkBuyer->company_buyer_id)? $checkBuyer->company_buyer_id : '';         
        $seller_id =  isset($checkBuyer->company_seller_id)? $checkBuyer->company_seller_id : ''; 
        $company_id =  isset($checkBuyer->company_id)? $checkBuyer->company_id : '';   
         if($buyer_id == $company_id && $buyer_id != '' && $company_id != ''){
            $user_type = 'buyer';
         }elseif($seller_id == $company_id && $seller_id != '' && $company_id != ''){
            $user_type = 'seller';
         }else{
            $user_type = '';
         }
         return $user_type;
    }
}
