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
use Config;
use App\Comment;
use App\Notification;
use DB;
use Carbon\Carbon;
use Storage;
use Hash;
use App\SpaceUser;
use Validator;
use App\User;
use App\Jobs\SendFeedbackNotification;
use App\Media;
use App\Space;
use App\Company;
use App\Feedback;
use App\Helpers\Aws;
use App\Helpers\Logger;
use App\Http\Controllers\MailerController;
use App\Http\Controllers\ManageShareController;
use App\Traits\Feedback as feedbackTrait;
use PDF;
use \Symfony\Component\HttpFoundation\Response as HttpResponse;

class FeedbackController extends Controller {

  use feedbackTrait;

  public function feedbackPopup(Request $request, $space_id) {
    
    if($this->giveFeed($space_id, Auth::user()->id)) return;
    $feedback_data['space'] = Space::getSpaceBuyerSeller($space_id);
    $feedback_data['user_id'] = Auth::user()->id;
    return view('feedback/modal/feedback_popup', ['feedback_data'=>$feedback_data, 'request_data' => $request->all()]);
  }

  public function feedback($month='', $year='',$space_id=null) {
    $space_id = $space_id??Session::get('space_info')['id'];
    if(($month != Carbon::now()->month) || ($year != Carbon::now()->year) ){
      (new Logger)->mixPannelInitial(Auth::user()->id, $space_id, Logger::MIXPANEL_TAG['past_quater']);
    }
    (new UserController)->updateSpaceSessionData($space_id);
    (new ManageShareController)->setClientShareList();
    if(isset($space_id)){
      $space = SpaceUser::where('user_id', Auth::user()->id)->where('space_id', $space_id)->whereRaw("metadata #>> '{user_profile}' != ''")->where('user_status','0')->orderBy('created_at', 'desc')->first();
    }
    if( !sizeOfCustom($space) ) {
      abort(404);
    }
    
    (new Logger)->log([
      'action' => 'visit feedback',
      'description' => 'visit feedback'
    ]);
    $user_id = Auth::user()->id;
    $feedback_on_off =  app('App\Http\Controllers\FeedbackController')->feedbackStatus($space_id);
    if(!$feedback_on_off['feedback_status']){
     return redirect('clientshare/'.$space_id);
    }
    if($month !=''){ $month = $month; }else{  $month = date('m'); }
    if($year !=''){ $year = $year; }else{   $year = date('Y'); }
    $give_feedback = $this->giveFeed($space_id,$user_id);
    $check_buyer = $this->checkBuyer($space_id,$user_id);
    $feedback_opened_till = Config::get('constants.feedback.feedback_opened_till');
    $day_check = Carbon::now()->timezone(\Auth::user()->timezone??'Europe/London')->day>$feedback_opened_till?'true':'false';
    $post_dated_or_isseller = ($month == Carbon::now()->month) && ($year == Carbon::now()->year)?false:true;
    $post_dated_or_isseller = $check_buyer == 'seller'?true:$post_dated_or_isseller;
    $data['check_user_buyer_or_seller'] = $check_buyer;
    $data['feedback_current_status'] = $this->feedback_current_status($space_id, $post_dated_or_isseller );   
    $feedback_quaters = $this->feedbackQuaters($space_id, ['feedback_opened_till'=> $feedback_opened_till], $data['feedback_current_status']);

    $default_latest_feedback = true;
    if(!empty($feedback_quaters)){
      foreach ($feedback_quaters as $key => $quater) {
        if(Carbon::parse($month.'/01/'.$year)->format('m - y') == Carbon::parse($quater->created_at)->format('m - y'))
          $default_latest_feedback = false;
      }
    }
    if(!empty($feedback_quaters) && $default_latest_feedback && !$data['feedback_current_status']['current_month_feedback_running']){
      $month = Carbon::parse(end($feedback_quaters)->created_at)->month;
      $year = Carbon::parse(end($feedback_quaters)->created_at)->year;
    }

    $get_non_feedback_user = $this->pendingFeedbackUsers($space_id, $month, $year);
    $feedback = $this->submittedFeedbackUsers($space_id, $month, $year);
    $data['current_quater'] = Carbon::parse($month.'/01/'.$year)->subMonth(3)->format('F Y').' - '.Carbon::parse($month.'/01/'.$year)->subMonth(1)->format('F Y');
    
    $data['feedback_current_status']['seller_view'] = ($data['check_user_buyer_or_seller'] == "seller" && ($month == Carbon::now()->month) && ($year == Carbon::now()->year) && $data['feedback_current_status']['current_month_eligible']) || (!$data['feedback_current_status']['total_feedback'] && !$data['feedback_current_status']['current_month_feedback_running']);

    if($data['feedback_current_status']['seller_view'])
      $data['feedback_current_status']['seller_view'] = !($data['feedback_current_status']['current_month_feedback_count'] && !$data['feedback_current_status']['current_month_eligible']);
    $data['feedback_current_status']['display_feedback'] = $data['feedback_current_status']['display_feedback'] && ((!$data['feedback_current_status']['seller_view'] && !$data['feedback_current_status']['current_month_eligible'])||($month != Carbon::now()->month));
    (new Logger)->mixPannelInitial(Auth::user()->id, $space_id, Logger::MIXPANEL_TAG['view_feedback']);
    return view('pages/feedback',['data'=>$data, 'feedback_quaters'=>$feedback_quaters, 'nps'=>$feedback['nps'],'feedback'=>$feedback['feedback'],'month'=> $month,'get_non_feedback_user' => $get_non_feedback_user,"giveFeedback"=>$give_feedback,'checkBuyer'=>$check_buyer,'space_id'=>$space_id]);
  }


  
  public function pendingFeedbackUsers($spaceId, $month, $year){
    $get_non_feedback_user = SpaceUser::with('User')
      ->where('space_id','=',$spaceId)
      ->where('user_status','=',0)
      ->where('metadata->invitation_code','=',1)
      ->whereNotIn('user_id',function($query)use($year,$month,$spaceId){
            $query->select('user_id')->from('feedback')
            ->where('space_id','=',$spaceId)
            ->whereYear('created_at', '=', $year)
            ->whereMonth('created_at', '=', $month);
        })
      ->get()->toArray();
      return $get_non_feedback_user;
  }

  
  public function submittedFeedbackUsers($spaceId, $month, $year){
    $feedback = Feedback::submittedFeedbackUsers($spaceId, $month, $year);
    $vgood  = array();
    $good   = array();
    $bad    = array();
    $currentUser = array();
    $allUser = array();
    if(!empty($feedback)){
      foreach ($feedback as $val) {
        if($val->user_id == Auth::user()->id){
          array_push($currentUser, $val);
        }else{
          array_push($allUser, $val);
        }
        /*Calculation for NPS*/
        if($val->rating > 8) {
          $vgood[] = $val->rating;
        } elseif($val->rating > 6 && $val->rating < 9) {
          $good[] = $val->rating;
        } else {
          $bad[] = $val->rating;
        }
      }
      $feedback = array_merge($currentUser, $allUser);
      $vgood  = sizeOfCustom($vgood);
      $good   = sizeOfCustom($good);
      $bad    = sizeOfCustom($bad);
      $total  = $vgood + $good + $bad;
      $avgGoodResponse = ($vgood/$total)*100;
      $avgBadResponse = ($bad/$total)*100;
      $nps    = $avgGoodResponse - $avgBadResponse;
      $nps    = round($nps);
      /*End Calculation of NPS*/
    } else {
      $nps = '';
    }

    return ['feedback'=>$feedback, 'nps'=>$nps];
  }


  
  public function feedbackQuaters($space_id, $feedback=null, $feedback_data=null){
    $control_statement = Carbon::now()->day>$feedback['feedback_opened_till']||$feedback_data['current_month_feedback_running'];
    $feedback['show_current_month_quater'] = $control_statement ? 'or true':'';
    return Feedback::feedbackQuaters($space_id, $feedback['show_current_month_quater']);
  }

  
  public function saveFeedback(Request $request) {
    $data = $request->all();

    $space_post = \Validator::make($request->all(),
      ['rating'=>'required']
    );

    if($space_post->fails()){
      return ['code' => HttpResponse::HTTP_UNAUTHORIZED, 'message' => $space_post->errors()];
    }

    for($i=3;$i>0;$i--) {
      $feedback_month[] = Carbon::now()->subMonth($i);
    }
    $feed = new Feedback;
    $feed->space_id = $data['space_id'];
    $feed->user_id = $data['user_id'];
    $feed->rating = $data['rating'];
    $feed->suggestion = $data['suggestion'];
    $feed->comments = $data['comments'];
    $feed->feedback_month = $feedback_month;
    $saveData = $feed->save();
    if(isset($data['home'])) {
    return redirect('clientshare/'.$feed->space_id)->with('successfeedback','done');
    } else {
    return redirect('feedback')->with('successfeedback','done');
    }
  }


  /* */
  public function checkUserBuyerOrSeller($space_id,$user_id){
    $space_user = SpaceUser::getActiveSpaceUser($space_id,$user_id);
    if(!sizeOfCustom($space_user)) return '';
    $seller = Space::select('id')->where('id', $space_id)
      ->where('company_seller_id', $space_user[0]['user_company_id'])->get();
    $buyer = Space::select('id')->where('id', $space_id)
      ->where('company_buyer_id', $space_user[0]['user_company_id'])->get();
    if(sizeOfCustom($seller)) return 'seller';
    if(sizeOfCustom($buyer)) return 'buyer';
    return '';
  }


  
  function checkBuyer($space_id,$user_id){
    return $this->checkUserBuyerOrSeller($space_id,$user_id);
  }

  function giveFeed($space_id,$user_id){
          $give_feed = DB::table('feedback as f')
                  ->select('f.user_id')
                  ->where('f.space_id', '=', $space_id)
                  ->where('f.user_id', '=', $user_id)
                  ->whereYear('f.created_at', '=', date('Y'))
                  ->whereMonth('f.created_at', '=', date('m'))
                  ->get()->toArray();
      return sizeOfCustom($give_feed);
  }

  function feedbackOnOff($spid=null){
    if(!empty($spid)){
      $spaceId = $spid;
    }
    else{
      $spaceId = Session::get('space_info')['id'];
    }
    $spaceInfo = Space::find($spaceId);
    $notificatiodate = $spaceInfo->feedback_status_to_date;
     $notificatiostatus=$spaceInfo->feedback_status;
     $feddback_current_status=$spaceInfo->feddback_current_status;
    if(!empty($notificatiostatus)){
      $status = '1';
    }else{
      $status = '0';
    }
    if(!empty($feddback_current_status)){
      $cur_status = '1';
    }else{
      $cur_status = '0';
    }
    if($notificatiodate){
       $date=strtotime(date('Y-m-d h:i:s'));
       $notidate=strtotime($notificatiodate);
       if($date <= $notidate && $feddback_current_status == 'TRUE'){
          $feedbackshow='true';
       }elseif($date <= $notidate && $feddback_current_status == 'FALSE'){
          $feedbackshow='false';
       }elseif($date >= $notidate && $status == 1){
          $feedbackshow='true';
       }elseif($date >= $notidate && $status == 0){
          $feedbackshow='false';
       }elseif($date <= $notidate && $status == 1){
          $feedbackshow='false';
       }elseif($date <= $notidate && $status == 0){
          $feedbackshow='false';
       }else{
          $feedbackshow='true';
       }
    }else{
      if($status == 1){
        $feedbackshow='true';
      }else{
        $feedbackshow='false';
      }
    }
    return $feedbackshow;
  }


  /* */
  public function feedbackStatus($space_id) {
    space::findorfail($space_id);
    $feedback_opened_till = Config::get('constants.feedback.feedback_opened_till');
    $space = Space::where('id', $space_id)->get()[0];
    $user_current_quater_feedback = Feedback::where('space_id', $space_id)
      ->where('user_id', Auth::user()->id)
      ->whereRaw("to_char(created_at, 'mm') = to_char(now(), 'mm')")
      ->get();
    $space['user_current_quater_feedback'] = $user_current_quater_feedback;
    $space['total_feedback'] = Feedback::spaceFeedbacks($space_id,'count');
    $space['next_due'] = $space['feedback_status_to_date'];
    $space['current_month_eligible'] = Carbon::parse($space['next_due'])->format('m - y') == Carbon::now()->format('m - y') && Carbon::now()->day<=$feedback_opened_till;
    $space['current_month_eligible_v2'] = Carbon::parse($space['next_due'])->format('m - y') == Carbon::now()->format('m - y');
    $space['window_status'] = ($feedback_opened_till - Carbon::now()->day )<0?false:true;
    $space['days_left'] = ($feedback_opened_till - Carbon::now()->day)+1;
    $space['given_feedback'] = Feedback::submittedFeedbacks($space_id, 'count');
    $space['share_members'] = SpaceUser::spaceBuyers($space_id, 'count');
    return $space;
  }

  /* */
  public function feedback_current_status($space_id, $post_dated_or_isseller) {
    space::findorfail($space_id);
    $feedback_opened_till = Config::get('constants.feedback.feedback_opened_till');
    $space = Space::where('id', $space_id)->get()[0];
    $data['feedback_status'] = $space['feedback_status'];
    $data['feedback_current_status'] = $space['feedback_current_status'];
    $data['next_due'] = $space['feedback_status_to_date'];
    $data['current_month_eligible'] = Carbon::parse($data['next_due'])->format('m - y') == Carbon::now()->format('m - y') && Carbon::now()->day<=$feedback_opened_till;
    $data['current_month_feedback_running'] = Carbon::parse($data['next_due'])->format('m - y') == Carbon::now()->format('m - y');
    $data['total_feedback'] = Feedback::where('space_id', $space_id)->count();
    $user_current_quater_feedback = Feedback::where('space_id', $space_id)
      ->where('user_id', Auth::user()->id)
      ->whereRaw("to_char(created_at, 'mm') = to_char(now(), 'mm')")
      ->get();
    $data['next_due_month'] = Carbon::parse($data['next_due'])->month;
    $data['next_due_year'] = Carbon::parse($data['next_due'])->year;
    $data['give_feedback'] = !$post_dated_or_isseller && !sizeOfCustom($user_current_quater_feedback)
      && $data['next_due_month'] == Carbon::now()->month && $data['next_due_year'] == Carbon::now()->year
      && $feedback_opened_till-Carbon::now()->day >= 0;
    $data['display_feedback_tat'] = !$post_dated_or_isseller && Carbon::now()->day <= $feedback_opened_till && sizeOfCustom($user_current_quater_feedback)
      && $data['next_due_month'] == Carbon::now()->month && $data['next_due_year'] == Carbon::now()->year;
    $data['display_feedback'] = $post_dated_or_isseller || (!$data['give_feedback'] && !$data['display_feedback_tat']);
    $data['days_left'] = ($feedback_opened_till - Carbon::now()->day)+1;
    $data['current_month_feedback_count'] = Feedback::spaceFeedbacks($space_id, 'count');
    return $data;
  }

  /* */
  public function feedbackCloseNotification() {
    ini_set('max_execution_time', -1);
    /* Run schedular on specified date */
    if(Carbon::now()->day != Config::get('constants.feedback.close_feeback_day')) return 0;
    
    /* Fetching relevant Share, Users for notification & mail */
    $feedback_close_share_users_list = Feedback::feedbackCloseShareUsersList();
    foreach ($feedback_close_share_users_list as $key => $value) {
      Notification::create([
        'notification_type' => 'feedback_close',
        'space_id' => $value->space_id,
        'notification_status' => false,
        'user_id' => $value->u_id
      ]);
      $feedback_close_share_users_list['to'] = $value->email;
      $feedback_close_share_users_list['subject'] = "View Feedback for the ".$value->share_name." Relationship";
      $feedback_close_share_users_list['user_first_name'] = $value->first_name;
      $feedback_close_share_users_list['share_name'] = $value->share_name;
      $feedback_close_share_users_list['share_id'] = $value->space_id;
      $feedback_close_share_users_list['seller_logo'] = $value->seller_processed_logo;
      $feedback_close_share_users_list['buyer_logo'] = $value->buyer_processed_logo;
      $feedback_close_share_users_list['path'] = env('APP_URL');
      $first_ = Carbon::now()->subMonth(3);
      $last_ = Carbon::now()->subMonth(1);
      $feedback_close_share_users_list['quater'] = $first_->format('F Y')." - ".$last_->format('F Y') ;
      $feedback_close_share_users_list['view'] = 'email.feedback_close';
      (new MailerController)->feedbackCloseNotification($feedback_close_share_users_list);
    }
    
    $shares_for_logging = Space::sharesWithActiveFeedback();
    foreach ($shares_for_logging as $key => $value) {
      for($i=3;$i>0;$i--) {
        $feedback_month[] = Carbon::now()->subMonth($i);
      }
      (new Logger)->log([
        'action' => 'feedback close',
        'description' => 'log close feedback event',
        'space_id' => $value->id,
        'metadata' => $feedback_month
      ]);
      unset($feedback_month);
    }
    /* Pushing feedback to next quater */
    Space::whereRaw('feedback_status is true')
      ->whereRaw("to_char(feedback_status_to_date, 'mm') = to_char(now(), 'mm')")
      ->whereRaw("to_char(feedback_status_to_date, 'yy') = to_char(now(), 'yy')")
      ->update(['feedback_status_to_date'=>Carbon::now()->addMonth(3)]);

    return 1;
  }

  
  public function feedbackReminder($space_id) {
    space::findorfail($space_id);
    dispatch(new SendFeedbackNotification($space_id));
    $this->logReminder(['space_id'=> $space_id, 'user_id'=> Auth::user()->id]);
    return "Reminder Send";
  }
  
  
  public function feedbackAdminReminder() {
    if( !in_array(date('d'), Feedback::ADMIN_REMINDER_DAYS) ) return;
    $spaces = Feedback::pendingFeedbackSpaces();
    foreach ($spaces as $space ) {
      $admins = SpaceUser::spaceUsers($space, 'admin');
      foreach ($admins as $admin) {
        if( $this->checkRemiderStatus($space->id)['status'] ) continue;
        $space->feedback_submitted = Feedback::submittedFeedbacks($admin->space_id, 'count');
        $space->buyers = SpaceUser::spaceBuyers($admin->space_id, 'count');
        (new MailerController)->feedbackReminderShareAdmin(compact('admin', 'space'));
      }
    }
  }
}