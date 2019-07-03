<?php

namespace App\Http\Controllers;
use App\{PowerBiReport, SpaceUser, Space, User};
use Auth;
use Validator;
use Excel;
use Carbon\Carbon;
use Session;
use DB;
use Config;
use Redirect;
use App\ActivityLog;
use App\Helpers\{Logger, Aws};
use App\Http\Controllers\{ManageShareController, FeedbackController, MailerController};
use Illuminate\Http\Request;
use App\Jobs\BulkInvitations;
use App\Jobs\BulkInvitationUrls;
use App\Helpers\bulkInvitation;
use App\Invitation;
use App\Traits\{
  FileHandling,
  AwsS3,
  Feedback,
  Generic
};

class SettingController extends Controller {

  use FileHandling, AwsS3, Feedback, Generic;

    function __construct()  {
      $this->middleware(function ($request, $next) {
        if(Session::get('space_info') != null) return $next($request);
        $space_info = Space::getAllSpaceBuyerSeller($request->id??$request->space_id);
        if(!sizeOfCustom($space_info)) return redirect('/');
        $space_info = $space_info[0];
        $space_user = SpaceUser::getSpaceUserRole($request->id,Auth::user()->id);
        $space_info['space_user'] = $space_user;
        Session::put('space_info', $space_info);
        return Session::get('space_info') == null ? redirect('/') : $next($request);
      });
    }


    public function removeReport($space_id, $report_id){
      return (new PowerBiReport)->removeReport($space_id, $report_id);
    }

    public function powerReports($space_id) {
      $report_data = (new PowerBiReport)->shareReportList($space_id);
      return view('setting/power_bi', compact('report_data', 'space_id'));
    }

    public function getReportList($space_id) {
      return (new PowerBiReport)->shareReportList($space_id);
    }

    public function createreport(Request $request)
    {
      $this->validate($request, [
        'report_type' => 'required|max:100',
        'report_credentials.*' => 'required',
        'report_name' => 'required|max:100'
        ], [
        'required' => 'This field is required.'        
      ]);

      return PowerBiReport::create([
        'space_id'=> $request->space_id,
        'user_id'=> Auth::user()->id,
        'report_type'=> $request->report_type,
        'report_name'=> $request->report_name,
        'metadata'=> $request->report_credentials,
      ]);
    }

    public function moveCompanyLogo(){
      $columns = [
        ['old_column' => 'company_buyer_logo', 'new_column' => 'buyer_logo', 'wrapper_column' => 'buyer_logo_unwraped_url',],
        ['old_column' => 'company_seller_logo', 'new_column' => 'seller_logo', 'wrapper_column' => 'seller_logo_unwraped_url',]
      ];

      foreach($columns as $column){
        Space::moveLogo($column);
      }
    }

    /**/
    public function autoTriggerFeedbackReminder(Request $request, $space_id) {
      $remider_status = $this->checkRemiderStatus($space_id);
      if(!$remider_status['status']){
        (new FeedbackController)->feedbackReminder($space_id);
      }
      return $this->index($request, $space_id);
    }

    public function index(Request $request, $space_id) {
      (new UserController)->updateSpaceSessionData($space_id);
      (new ManageShareController)->setClientShareList(); 
      $get_landing_data = $request->all();
      if (isset($get_landing_data['email']) && base64_decode($get_landing_data['email']) != auth::user()->email && isset($get_landing_data['notification'])) {
         return redirect('logout?spaceid=' . $space_id . '&email=' . $get_landing_data['email'] . '&status=logout&from=setting&#notifications-tab');
      }


      $domain_management = Space::find($space_id);
      $user_setting = SpaceUser::getOneSpaceUserInfo($space_id,Auth::user()->id);
      $user_weekly_setting = User::getUserSettings(Auth::user()->id);
      if(isset(($user_weekly_setting[0])['weekly_summary_setting'])){
        $user_setting['weekly_alert'] = ($user_weekly_setting[0])['weekly_summary_setting'];
      }
      $feedback_status_to_date = space::getFeedbackStatus($space_id);
      $check_buyer =  (new FeedbackController)->checkBuyer($space_id,Auth::user()->id);
      $feedback_status =  (new FeedbackController)->feedbackStatus($space_id);
      $show_tab = $request->show_tab??'';
      $space_with_buyer_seller = Space::getSpaceBuyerSeller($space_id);
      $space_with_buyer_seller['feedback_reminder_status'] = $this->checkRemiderStatus($space_id);
      $s3_form_details = (new Aws)->uploadClientSideSetup();

      (new Logger)->mixPannelInitial(Auth::user()->id, $space_id, Logger::MIXPANEL_TAG['settings']);


      return view('setting/index', ['s3_form_details'=>$s3_form_details,'show_tab'=>$show_tab, 'feedback_status'=>$feedback_status,'domain_management'=>$domain_management, 'user_setting'=>$user_setting,'feedback_status_to_date' => $feedback_status_to_date,'checBuyer' => $check_buyer,'space_data'=>$space_with_buyer_seller, 'space_id'=>$space_id]);
    }

    public function userManagement(Request $request) {
      $space_id = $request['space_id'];
      if($space_id){
        $space_members = SpaceUser::getSpaceMembers($space_id);
        $space_members->setPath(url('/user_management?space_id='.$space_id,[],env('HTTPS_ENABLE', true)));
        $space_with_buyer_seller = Space::getSpaceBuyerSeller($space_id);
        $space_with_buyer_seller['feedback_reminder_status'] = $this->checkRemiderStatus($space_id);

        $user_role = SpaceUser::isAdmin($space_id, Auth::user()->id);
        if($user_role) {
          $space_user[] = [
            'user_role' => ['user_type_name' => array_search($user_role, \App\UserType::USER_TYPE)]
          ];
        }
        return view('setting/user_management', [
          'space_users' => $space_members,
          'space_data' => $space_with_buyer_seller,
          'space_user_data' => $space_user??[]
        ])->render();
      }
      return false;
    }

    public function pendingInvites(Request $request) {
           $space_id = $request['space_id'];
           if($space_id){
             $pending_invitations = SpaceUser::getPendingInvitations($space_id);
             $pending_invitations->setPath(url('/pending_invites?space_id='.$space_id,[],env('HTTPS_ENABLE', true)));
           if(!empty($pending_invitations)){
              foreach ($pending_invitations as $pending_invite_value) {
                $invited_by_list = ActivityLog::inviteList($pending_invite_value);
                $result['space_user'] = $pending_invite_value;
                $result['invited_by_list'] = $invited_by_list;
                $invited_by_users[] = $result;           
              }
            }
           if(!isset($invited_by_users)) $invited_by_users = [];
                return view('setting/pending_invites', ['pending_invitations_pagination'=>$pending_invitations,'pending_invitations'=> $invited_by_users])->render();
           }
           return false;
    }
    
    public function cancelInvitation($id) {
        $space_user = SpaceUser::findOrFail($id);
        $metadata = $space_user['metadata'];
        $metadata['invitation_status'] = SpaceUser::STATUS['invitation']['message']['canceled'];
        $metadata['invitation_code'] = SpaceUser::STATUS['invitation']['code']['canceled'];
        (new Logger)->log([
            'action'     => SpaceUser::STATUS['invitation']['log_message']['canceled'],
            'description' => SpaceUser::STATUS['invitation']['log_message']['canceled']
        ]);
        return SpaceUser::updateUserDataInSpaceUser($space_user['space_id'], $space_user['user_id'], ['metadata' => json_encode($metadata)]);
    }
    
    public function domain_update(Request $request){
        return $request->all();
    }

    public function notificationSettings(Request $request){
      SpaceUser::updateByUserSpace(Auth::user()->id, $request->space_id, $request->space_user);
      
      $setting['settings'] = json_encode(["weekly_summary_setting"=>$request->weekly_check_box]);
      User::updateUser(Auth::user()->id, $setting);

      (new Logger)->log([
        'action'     => 'change notification setting',
        'description' => 'change notification setting'
      ]);
      return $this->logSettings($request->space_user, $request->all());
    }

    public function logSettings($userdata, $request_data){

      $space_user = SpaceUser::getSpaceUserInfo($request_data['space_id'],$request_data['user_id'], 'first');

      if($space_user['post_alert'] != $userdata['post_alert']){
        $event_tags[] = $userdata['post_alert']?Logger::MIXPANEL_TAG['post_alert_on']:Logger::MIXPANEL_TAG['post_alert_off'];
      }

      if($space_user['comment_alert'] != $userdata['comment_alert']){
        $event_tags[] = $userdata['comment_alert']?Logger::MIXPANEL_TAG['comment_alert_on']:Logger::MIXPANEL_TAG['comment_alert_off'];
      }

      if($space_user['like_alert'] != $userdata['like_alert']){
        $event_tags[] = $userdata['like_alert']?Logger::MIXPANEL_TAG['like_alert_on']:Logger::MIXPANEL_TAG['like_alert_off'];
      }
      
      if($space_user['invite_alert'] != $userdata['invite_alert']){
        $event_tags[] = $userdata['invite_alert']?Logger::MIXPANEL_TAG['invite_alert_on']:Logger::MIXPANEL_TAG['invite_alert_off'];
      }      

      @array_walk($event_tags, function($tag)use($request_data){
        (new Logger)->mixPannelInitial(Auth::user()->id, $request_data['space_id'], $tag);
      });

    }

    public function allowPosting(Request $request_data){
      $allow_seller_post = isset($request_data->seller)?true:false;
      $allow_buyer_post = isset($request_data->buyer)?true:false;
      $allow_invite_permission = isset($request_data->invite_permission)?true:false;
      if(Space::spaceById($request_data->space_id, 'first')->allow_seller_post != $allow_seller_post)
        (new Logger)->mixPannelInitial(Auth::user()->id, $request_data->space_id, $allow_seller_post?'Seller post enable':'Seller post disable');

      if(Space::spaceById($request_data->space_id, 'first')->allow_buyer_post != $allow_buyer_post)
        (new Logger)->mixPannelInitial(Auth::user()->id, $request_data->space_id, $allow_buyer_post?'Buyer post enable':'Buyer post disable');
       if(Space::spaceById($request_data->space_id, 'first')->invite_permission != $allow_invite_permission)
        (new Logger)->mixPannelInitial(Auth::user()->id, $request_data->space_id, $allow_invite_permission?'Restrict Invites':'Unrestrict Invites');
      
      Space::updateSpaceById($request_data->space_id, ['allow_seller_post'=>$allow_seller_post, 'allow_buyer_post'=> $allow_buyer_post,'invite_permission'=>$allow_invite_permission]);

      (new Logger)->log([
        'description' => 'change company permission',
        'action' => 'change company permission for posting'
      ]);
   return Redirect::to('/setting/'.$request_data->space_id.'#permissions-tab');
    }

    public function feedbackSetting(Request $request) {
      $request_data = $request->all();

      if($request_data['feedback_on_of'] == 'TRUE' && empty($request_data['feedback_type']))
          abort(400);

      $feedback_data = array(
        'feedback_status' => $request_data['feedback_on_of'],
        'feedback_status_to_date' => $request_data['feedback_type']?$request_data['feedback_type']:null
      );
      Space::updateSpaceById($request_data['space_id'], $feedback_data);

      (new Logger)->log([
        'action' => 'enable|disable feedback',
        'description' => 'enable|disable feedback'
      ]);
      $event_tag = strtolower($request->feedback_on_of) == config('constants.REQUESTED_FORM.status.true')?Logger::MIXPANEL_TAG['enable_feedback']:Logger::MIXPANEL_TAG['disable_feedback'];
      (new Logger)->mixPannelInitial(Auth::user()->id, $request_data['space_id'], $event_tag);
    }

    public function feedbackDateUpdate(Request $request) {
      $request_data = $request->all();
      $space_id = $request_data['space_id'];
      $year = $request_data['tempyear'];
      $month = $request_data['tempmonth'];
      $day = $request_data['tempday'];
      $space = Space::spaceById($space_id, 'first');
      $current_timestamp = strtotime(date('Y-m-d h:i:s'));
      $current_date = date("$year-$month-$day h:i:s");
      echo strtotime($current_date);echo "<br>";
      if(!empty($space->feedback_status_to_date)){
          if(strtotime($current_date) <= $current_timestamp){
              if($space->feddback_current_status == 'TRUE'){
                  $current_status = 'FALSE';
              }else{
                  $current_status = 'TRUE';
              }
          } else {
              $current_status = $space->feddback_current_status;
          }
      }else{
          $current_status = $space->feddback_current_status;
      }
      $feedback_data = array(                    
          'feddback_current_status' => $current_status, 
          'feedback_status_to_date' => $current_date 
      );
      Space::updateSpaceById($space_id, $feedback_data);
    }

    public function companyUpdate(Request $request){
        $data = $request->all();
        $company = $data['company_id'];
        $user = $data['user_id'];
        $space = $data['space_id']??Session::get('space_info')['id'];
        $space_data = SpaceUser::getSpaceUserInfo($space, $user, 'first');
        $metadata = $space_data->metadata;
        $user_profile = $metadata['user_profile'] = ['company'=>$company];  
        unset($metadata['user_profile']);     
        $space_user_profile['user_profile'] = array_merge($space_data->metadata['user_profile'],$user_profile);
        $space_user_metadata = array_merge($metadata,$space_user_profile);  
        SpaceUser::updateByUserSpace($user, $space, ['metadata' => json_encode($space_user_metadata),'user_company_id'=>$company]);
        (new UserController)->updateSpaceSessionData($space);       
        (new ManageShareController)->setClientShareList();        
        $space_info = Session::get('space_info');
        if(!empty(Session::get('space_info')['sub_companies'])){
            $checkBuyer =  (new FeedbackController)->checkBuyer($space,$user);        
            if($checkBuyer == Config::get('constants.USER.role_tag.buyer')){
              SpaceUser::updateByUserSpace($user, $space,['sub_company_id'=>'00000000-0000-0000-0000-000000000001']);
            }       
            if($checkBuyer == Config::get('constants.USER.role_tag.seller') ){
              SpaceUser::updateByUserSpace($user, $space,['sub_company_id'=>'00000000-0000-0000-0000-000000000000']);          
            }
        }
      (new Logger)->log([
        'action' => 'update user company',
        'description' => 'update user company'
       ]);
      (new Logger)->mixPannelInitial(Auth::user()->id, $space, Logger::MIXPANEL_TAG['company_update']);
    }

    /**/
    public function sendBulkInvitation($data, $user_invite = null){
      $session_space_info = Session::get('space_info')->toArray();
      $session_space_info['sender_user']['id'] = Auth::user()->id;
      $session_space_info['sender_user']['email'] = Auth::user()->email;
      $session_space_info['sender_user']['first_name'] = Auth::user()->first_name;
      $session_space_info['sender_user']['last_name'] = Auth::user()->last_name;
      $this->dispatch(new BulkInvitations($data, $session_space_info, $user_invite));
      return;
    }

    public function checkDomainValidationForBulkInvites($request_data){ 
        $share = Space::find($request_data['share_id']);
        $metadata_rules = array();
        $tags = "";
        if ($share['domain_restriction'] && isset($request_data['bulk_invitation_file'])) {
            if(isset($share['metadata']['rule'])){
                foreach ($share['metadata']['rule'] as $v) {
                    $metadata_rules[] = '@' . $v['value'];
                }
                $tags = implode(', ', $metadata_rules);
            }
        }
        $user_name = SpaceUser::getUserBySpaceId($request_data['share_id'],Config::get('constants.USER_ROLE_ID'));
        $user_emails = array();
        foreach ($user_name as $val) {
            $user_emails[] = $val['user']['email'];
        }
        $username = implode(', ', $user_emails);
        if ($share['domain_restriction'] && isset($request_data['bulk_invitation_file']) && !isset($request_data['resend_mail']) && isset($share['metadata']['rule'])) {  
             $invite_user_emails = $domain_rule = array();
             foreach ($share['metadata']['rule'] as $key => $value) {
                      $domain_rule[] = strtolower($value['value']);
             }
              if(!empty($domain_rule)){
                foreach($request_data['users'] as $users){  
                  $invitee_email = explode("@", $users['email']);
                  if(isset($invitee_email[1]) && !in_array(strtolower($invitee_email[1]), $domain_rule)) $invite_user_emails[] = $users['email'];
                }
              }
              if(!empty($invite_user_emails)) $invite_emails = array_unique($invite_user_emails);
              if (!empty($invite_user_emails) && isset($request_data['bulk_invitation_file'])) {
                  $invite_emails = implode(', ', $invite_emails);
                  return ['code' => 403, 'message' => 'Invalid Email Domains in csv: your Client Share has been locked down to ' . $tags . '. Email addresses list in csv outside the approved domain(s).: '.$invite_emails];
              }
        }
        return ['code' => 200, 'message' => 'success'];
    }

    /**/
    public function sendInvitations(Request $request){
         $request_data = $request->all();  
      $request_data['users'] = json_decode($request_data['finalized_data'], true);
      $check_domain_validation = $this->checkDomainValidationForBulkInvites($request_data);
      if(isset($check_domain_validation['code']) && $check_domain_validation['code'] == 403){
         return redirect('/setting/'.$request_data['share_id'].'#bulk-invitation-tab')->with('bulk_status_msg', $check_domain_validation['message']);
      }
      if($request_data['user_invite'] == 'invite-export'){
        $this->exportBulkInvitation($request_data);
        $this->sendBulkInvitation($request_data,$request_data['user_invite']); 
        (new Logger)->mixPannelInitial(Auth::user()->id, $request_data['share_id'], Logger::MIXPANEL_TAG['generate_bulk_url_invite']);
        return redirect('/setting/'.$request_data['share_id'].'#bulk-invitation-tab')->with('bulk_status_msg', trans('messages.user_invitation.export_bulk_invitations', ['user_email' => Auth::user()->email])); 
      }else{
        $this->sendBulkInvitation($request_data);    
      }    
      (new Logger)->mixPannelInitial(Auth::user()->id, $request_data['share_id'], Logger::MIXPANEL_TAG['bulk_invite']);
      return redirect('/setting/'.$request_data['share_id'].'#bulk-invitation-tab')->with('bulk_status_msg', trans('messages.user_invitation.bulk_invitations'));
    }

    /* */
    public function bulkInvitations(Request $request) {
      $data = $request->all();
      $file = json_decode($data['bulk_invitation_file'], true)[0];
      $url = $this->signed_url($file['s3_name'], $file['mimeType']);
      $users = $this->read_file($url);

      if( sizeOfCustom( $users ) > Config::get('constants.BULK_INVITATION_USER_LIMIT') ){
        return ['error'=>true, 'message'=>'The maximum number of invites that can be sent in one file is '.Config::get('constants.BULK_INVITATION_USER_LIMIT').'. Please upload a smaller CSV list.'];
      }

      if(empty($users))
          return ['error'=>true, 'message'=>'CSV property/type not in a correct format. Please use text CSV format and upload it again.'];

      if(sizeOfCustom( $invalid_header = array_diff(array_keys($users[0]), ['first_name', 'last_name', 'email'])) )
        return ['error'=>true, 'message'=>'Invalid file headers. Followings are invalid header(s): '.implode(', ', $invalid_header)];

      foreach($users as $key => $user) {

        $validator = Validator::make($user, [
            'first_name' => array('required','max:25'),
            'last_name' => array('required','max:25'),
            'email' => array('required'),
          ], [
            'required' => 'This field is required',
            'first_name.max' => 'First name cannot be greater than 25 characters',
            'last_name.max' => 'Last name cannot be greater than 25 characters',
            'email' => 'Invalid email address.',
          ]
        );

        $status[$key] = $user;
        $status[$key]['status'] = $validator->errors()??true;
      }
      return $status;
    }
    public function exportBulkInvitation($invite_users) {
      if(!empty($invite_users['users'])){
        $invite_users['user'] = Auth::user();       
         return dispatch(new \App\Jobs\BulkInvitationUrls($invite_users));  
      }
    }
    public function userAddShare(Request $request){
      $request_data = $request->all();    
      $uploaded_file = $request->file('user_list');  
      if(empty($uploaded_file)){
        return ['success'=>false, 'message' => "please upload the csv file"];
      }
      $file = fopen($uploaded_file,"r");
      $column=fgetcsv($file);         
      while(!feof($file)){
        $row_data[] = fgetcsv($file);          
      }
      if(!isset($request_data['email'])){
        return ['success'=>false, 'message' => "email doesn't exist"];
      }
      $invited_by = User::getUserIdFromEmail($request_data['email']);
      if(empty($invited_by)){
        return ['success'=>false, 'message' => "This user doesn't exist"];
      }
      $share_rank_5 = (new bulkInvitation)->getShareWithRank(5);
      $share_rank_4 = (new bulkInvitation)->getShareWithRank(4);
      $share_rank_3 = (new bulkInvitation)->getShareWithRank(3);
      $share_rank_2 = (new bulkInvitation)->getShareWithRank(2);
      $share_rank_1 = (new bulkInvitation)->getShareWithRank(1);
      foreach ($row_data as $value) {
        if(!empty($value)){
          $user = User::getUserIdFromEmail($value[2]);
          if(!empty($user)){
            $space_user = SpaceUser::getSpaceUserInfo($share_rank_5['id'],$user['id'], 'first');  
            if(!empty($space_user)){
            $data = [              
            'user_id' =>$user['id'], 
            'user_type_id' => 3, 
            'metadata' => $space_user['metadata'],
            'user_company_id' => $space_user['user_company_id'],
            'sub_company_id' => $space_user['sub_company_id'],
            'created_by'=>$invited_by['id'],
            'doj' => Carbon::now()
            ];           
            if(!empty($value[4])){
             SpaceUser::userSaveInShare($data,$share_rank_4['id']);
            }
            if(!empty($value[5])){
             SpaceUser::userSaveInShare($data,$share_rank_3['id']);
            }
            if(!empty($value[6])){
             SpaceUser::userSaveInShare($data,$share_rank_2['id']);
            }
            if(!empty($value[7])){            
             SpaceUser::userSaveInShare($data,$share_rank_1['id']);
            }
          }
        }    
        }   
      }
      return redirect('/');
    }
}