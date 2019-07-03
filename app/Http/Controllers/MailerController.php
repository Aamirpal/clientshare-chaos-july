<?php

namespace App\Http\Controllers;
use Auth;
use Mail;
use Session;
use App\{User, Space, Company};
use Validator;
use Config;
use Illuminate\Http\Request;
use \Symfony\Component\HttpFoundation\Response as HttpResponse;

class MailerController extends Controller {

  /**/
  public function sendMail($mail_data){
    $sender_name = env("SENDER_NAME");
    if(isset($mail_data['sender_name'])){
        $sender_name = $mail_data['sender_name'].' [via Client Share]';
    }
    return Mail::send($mail_data['template'], ['mail_data'=>$mail_data], function ($message) use($mail_data,$sender_name){
      $message->from(env("SENDER_FROM_EMAIL"), $sender_name);
      $message->to( $mail_data['to'] );
      $message->subject( $mail_data['subject'] );
      if(isset($mail_data['reply_to'])) $message->replyTo($mail_data['reply_to']);
      foreach ( $mail_data['mail_headers']??[] as $header_key => $header ) {
        $message->getSwiftMessage()->getHeaders()->addTextHeader($header_key, $header);
      }
    });
  }

  /**/
  public function feedbackReminderShareAdmin($mail_data){
    $mail_data = json_decode(json_encode($mail_data), true);
    $mail_data['template'] = 'email.feedback_reminder_to_space_admin';
    $mail_data['to'] = $mail_data['admin']['user']['email'];

    $mail_data['subject'] = trans('messages.mail_subject.feedback_reminder_mail', ['buyer' => Company::getCompanyById($mail_data['space']['company_buyer_id'])['company_name']]);
    $mail_data['path'] = Config::get('constants.email.image_domain');
    $mail_data['days_left'] = Config::get('constants.feedback.feedback_opened_till') - date('d');
    $mail_data['send_reminder_link'] = $mail_data['path'] . "/auto_feedback_reminder/" . $mail_data['space']['id'] . "?email=".base64_encode($mail_data['to']). '&via_email=1&tab_name=feedback-tab&intended=true&id='.$mail_data['space']['id'];
    $mail_data['mail_headers'] = $mail_data['mail_headers']??[
      'X-PM-Tag' => 'feedback-alert',
      'space_id' => $mail_data['space']['id']
    ];
    return $this->sendMail($mail_data);
  }

  /**/
  public function bulkInvitationReport( $mail_data ){
    Mail::send('email.bulk_invitation_report', ['data'=>$mail_data], function ($message) use($mail_data){
      $message->from(env("SENDER_FROM_EMAIL"), env("SENDER_NAME"));
      $message->to($mail_data['sender_user']['email']);
      $message->subject('Bulk Invitation Report');
    });
  }

  /**/
  public function mailgunDrop( $mail_data ) {

    if ($mail_data['parsed_mail_data']['To'] == env("SENDER_FROM_EMAIL")) return 0;
    if( !isset($mail_data['parsed_mail_data']) || !isset($mail_data['parsed_mail_data']['Reply-To']) )return 0;
    $validator = Validator::make($mail_data, [
      'parsed_mail_data.Reply-To'=> array('email')
      ]);
    if ($validator->fails()) {
      return 0;
    }
     Mail::send('email.mailgun_drop', ['data'=>$mail_data], function ($message) use ($mail_data){
      $message->from(env("SENDER_FROM_EMAIL"), env("SENDER_NAME"));
      $message->to($mail_data['parsed_mail_data']['Reply-To']);
      $message->subject($mail_data['parsed_mail_data']['Subject']);
    });
    return response()->json(['message' => 'Request completed']);
  }

  /* */
  public function postmarkDrop( $mail_data ) {
    if( !sizeOfCustom($mail_data) || !isset($mail_data['ReplyTo']) )return 0;
    $validator = Validator::make($mail_data, ['ReplyTo'=> array('email')]);
    if ($validator->fails()) return 0;
    $mail_data['mail_subject'] = trans('messages.mail_subject.postmark_drop_mail', ['share_name' =>  Space::findOrFail($mail_data['space_id'])->share_name ]);
    Mail::send('email.postmark_drop', ['data'=>$mail_data], function ($message) use ($mail_data){
      $message->from(env("SENDER_FROM_EMAIL"), env("SENDER_NAME"));
      $message->to($mail_data['ReplyTo']);
      $message->subject($mail_data['mail_subject']);
    });
    return response()->json(['message' => 'Request completed']);
  }

  /**/
  public function spaceInvitation($user_id, $share_id,$mail_data)  {
    $user = User::find($user_id);
    if(!$user) return response()->json(['message' => 'Request Incomplete!!!']);
    $domain_name = env('APP_URL');
    $user['link'] = $domain_name."/registeruser/".$user->id."/".$share_id."?email=".base64_encode($user->email)."&alert=true";
    $user['share_name'] = $mail_data['share_name'];
    if(!empty($mail_data['seller_processed_logo'])) {
      $company_seller_logo = $mail_data['seller_processed_logo'];
    }else{
      $company_seller_logo = env('APP_URL').'/images/login_user_icon.png';
    }
    if(!empty($mail_data['buyer_processed_logo'])) {
      $company_buyer_logo = $mail_data['buyer_processed_logo'];
    }else {
      $company_buyer_logo = env('APP_URL').'/images/login_user_icon.png';
    }
    $user['company_seller_logo'] = $company_seller_logo;
    $user['company_buyer_logo'] = $company_buyer_logo;
    $user['path'] = env('APP_URL');
    $subject = 'The '.$mail_data['share_name'].' Client Share - Your invitation to the '.$mail_data['share_name'].' Client Share';
     Mail::send('email.share_invitation', ['data'=>$user], function ($message) use ($user,$subject, $share_id){
      $message->from(env("SENDER_FROM_EMAIL"), env("SENDER_NAME"));
      $message->to($user->email);
      $message->subject($subject);
      $message->getSwiftMessage()->getHeaders()->addTextHeader('space_id', $share_id);
      $message->replyTo(Auth::user()->email??env("SENDER_FROM_EMAIL"));
    });
    return response()->json(['message' => 'Request completed']);
  }


  public function shareJamesInvitation($user_id, $share_id) {
    $user = User::find($user_id);
    if(!$user) return response()->json(['message' => 'Request Incomplete!!!']);
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $user['added_by'] = $user->first_name.' '.$user->last_name;
    $user['path'] = env('APP_URL');
     Mail::send('email.share_james_invitation', ['data'=>$user], function ($message) use ($user, $share_id){
      $message->from(env("SENDER_FROM_EMAIL"), env("SENDER_NAME"));
      $message->to($user->email);
      $message->subject("Thank you for joining Client Share ");
      $message->getSwiftMessage()->getHeaders()->addTextHeader('space_id', $share_id);
       $message->getSwiftMessage()->getHeaders()->addTextHeader('X-PM-Tag', 'welcome-email');
    });
    return response()->json(['message' => 'Request completed']);
  }

  public function userInvitation($user_id, $share_id, $view=null, $mail_data=null) {
    $domain_name = env('APP_URL');
    $mail_data['cancel_invitation'] = $domain_name."/cancel_invitation_from_mail/".$mail_data['invitation_id']['space_user_id']??0;
    if(!empty($mail_data['mail']['company_seller_logo'])) {
      $company_seller_logo = $mail_data['mail']['company_seller_logo'];
    } else {
      $company_seller_logo = env('APP_URL').'/images/login_user_icon.png';
    }
    
    if(!empty($mail_data['mail']['company_buyer_logo'])) {
      $company_buyer_logo = $mail_data['mail']['company_buyer_logo'];
    } else {
      $company_buyer_logo = env('APP_URL').'/images/login_user_icon.png';
    }

    $mail_data['mail']['company_seller_logo'] = $company_seller_logo;
    $mail_data['mail']['company_buyer_logo'] = $company_buyer_logo;
    $mail_data['mail']['link'] = $domain_name.$mail_data['mail']['link']."?email=".base64_encode($mail_data['mail']['to'])."&invite=true";
    $mail_data['mail']['path'] = env('APP_URL');
    $subject = $mail_data['mail']['subject'];
    $mail_data['template'] = $view;
    $mail_data['to'] = $mail_data['mail']['to'];
    $mail_data['subject'] = $subject;
    $mail_data['sender_name'] = $mail_data['mail']['sender_first_name'].' '.$mail_data['mail']['sender_last_name'];
    $mail_data['mail_headers'] = $mail_data['mail_headers']??[
      'X-PM-Tag' => 'user-invitation',
      'space_id' => $share_id
    ];
    $mail_data['reply_to'] = Auth::user()->email??env("SENDER_FROM_EMAIL");
    $this->sendMail($mail_data);
    return response()->json(['code'=>0, 'message' => 'Request completed']);
  }

/* Send feedback close notification */
  public function feedbackCloseNotification($data){
    $data['path'] = env('APP_URL');
    Mail::send($data['view'], ['data'=>$data], function ($message) use ($data) {
      $message->from(env("SENDER_FROM_EMAIL"), env("SENDER_NAME"));
      $message->to( $data['to'] );
      $message->subject($data['subject']);
      $message->getSwiftMessage()->getHeaders()->addTextHeader('space_id', $data['share_id']);
      $message->replyTo(Auth::user()->email??env("SENDER_FROM_EMAIL"));
    });
    return response()->json(['code'=>0, 'message' => 'Request completed']);
  }

  public function feedbackNotification($user_id=null, $share_id=null, $view=null, $mail_data=null) {
    $mail_data['template'] = $view;
    $mail_data['to'] = $mail_data['to'];
    $mail_data['subject'] = trans('messages.mail_subject.feedback_notification', ['share_name' => $mail_data['space_user']['share']['share_name'] ]);
    $mail_data['path'] = Config::get('constants.email.image_domain');
    $mail_data['reply_to'] = Auth::user()->email??env("SENDER_FROM_EMAIL");
    $mail_data['mail_headers'] = [
      'space_id' => $mail_data['space_user']['space_id'],
      'X-PM-Tag' => 'feedback-alert'
    ];
    $this->sendMail($mail_data);
    return response()->json(['code'=>0, 'message' => 'Request completed']);
  }

  public function sendCommentEmailAlert($mail_data, $view){
    
    $mail_data['template'] = $view;
    $mail_data['to'] = $mail_data['user']['email'];
    $mail_data['current_user']['username'] = ucfirst($mail_data['current_user']['first_name']).' '.ucfirst($mail_data['current_user']['last_name']);
    $mail_data['subject'] = $mail_data['mail_subject']?? 'New comment from '.$mail_data['current_user']['username'];
    $mail_data['path'] = Config::get('constants.email.image_domain');
    $mail_data['reply_to'] = env("SENDER_FROM_EMAIL");

    $mail_data['spaceUserlink'] = env('APP_URL')."/clientshare/".$mail_data['space']['id'].'/'.$mail_data['post']['id'].'?email='.base64_encode($mail_data['user']['email']).'&alert=true&via_email=1';
    $mail_data['spaceloginlink']= env('APP_URL')."/clientshare/".$mail_data['space']['id'].'?email='.base64_encode($mail_data['user']['email']).'&alert=true&via_email=1';
    $mail_data['unsubscribe_share'] = env('APP_URL') . "/setting/" . $mail_data['space']['id'] . "?email=".base64_encode($mail_data['user']['email']). '&alert=true&via_email=1&notification=1&tab_name=notifications-tab';
    $mail_data['space_url'] = env('APP_URL')."/clientshare/".$mail_data['space']['id'];
    $mail_data['seller_logo'] = !empty($mail_data['space']['seller_processed_logo']) ?
                    composeEmailURL($mail_data['space']['seller_processed_logo']) ?? composeEmailURL($mail_data['space']['seller_processed_logo']) :
                    asset('/images/cs_logo.png');
    $mail_data['buyer_logo'] = !empty($mail_data['space']['buyer_processed_logo']) ?
                    composeEmailURL($mail_data['space']['buyer_processed_logo']) ?? composeEmailURL($mail_data['space']['buyer_processed_logo']) :
                    asset('/images/cs_logo.png');
    $mail_data['mail_headers'] = $mail_data['mail_headers']??[
      'X-PM-Tag' => 'comment-alert',
      'space_id' => $mail_data['space']['id']
    ];
    
    if(!strlen($mail_data['user']['email']) || $mail_data['user']['email'] == '') return 0;
    return $this->sendMail($mail_data);
  }  

  public function memberAccept($mail_data) {
    $mail_data['path'] = env('APP_URL');
    $user = User::find($mail_data['sender_user']);
    $mail_data['sender'] = $user;
    $mail_data['unsubscribe_share'] = env('APP_URL') . "/setting/" . $mail_data['space_id'] . "?email=".base64_encode($user->email). '&alert=true&via_email=1&notification=1&tab_name=notifications-tab';

    $mail_data['space_link'] = env('APP_URL')."/community_members/".$mail_data['space_id'].'/'.'?email='.base64_encode($user->email).'&alert=true&via_email=1';
    $mail_data['spaceloginlink']= env('APP_URL')."/clientshare/".$mail_data['space_id'].'?email='.base64_encode($user->email).'&alert=true&via_email=1';
    Mail::send($mail_data['view'], ['mail_data'=>$mail_data], function ($message) use ($user, $mail_data){
      $message->from(env("SENDER_FROM_EMAIL"), env("SENDER_NAME"));
      $message->to($user->email);
      $message->subject("Invite Accepted Notification");
      $message->getSwiftMessage()->getHeaders()->addTextHeader('space_id', $mail_data['space_id']);
      $message->getSwiftMessage()->getHeaders()->addTextHeader('X-PM-Tag', 'invite-accepted');
    });
  }

  public function weeklyEmail($view,$data,$data_array) {
    $data['path']= env('APP_URL');
    $subject = trans('messages.weekly_status.weekly_status_message');
     Mail::send($view, ['data'=>$data,'user_info'=>$data_array], function ($message) use ($data,$subject) {
      $message->from(env("SENDER_FROM_EMAIL"), env("SENDER_NAME"));
      $message->to( $data['to'] );
      $message->subject($subject);
      $message->replyTo(env('SENDER_FROM_EMAIL',Config::get('constants.email.reply_to')));
      $message->getSwiftMessage()->getHeaders()->addTextHeader('space_id', $data['space_id']);
      $message->getSwiftMessage()->getHeaders()->addTextHeader('X-PM-Tag', 'weekly-summary');
    });
    return response()->json(['code'=>0, 'message' => 'Request completed']);
  }


  public function pendingEmail($view, $mail_data) {
    $mail_data['template'] = $view;
    $mail_data['to'] = $mail_data['user']['email'];
    $mail_data['subject'] = trans('messages.mail_subject.pending_invites_reminder', ['share_name' => $mail_data['share']['share_name'] ]);
    $mail_data['path'] = Config::get('constants.email.image_domain');
    $mail_data['user_mail'] = $mail_data['path'].'/registeruser/'.$mail_data['user_id'].'/'.$mail_data['space_id']."?email=".base64_encode($mail_data['user']['email'])."&invite=true";
    $mail_data['cancel_mail'] = $mail_data['path']."/cancel_invitation_from_mail/".$mail_data['id'];
    $mail_data['reply_to'] = env("SENDER_FROM_EMAIL");
    if(!strlen($mail_data['user']['email']) || $mail_data['user']['email'] == '') return 0;
    return $this->sendMail($mail_data);
  }


  public function deleteShareEmail($view,$data) {
    $data['path']= env('APP_URL');
    $subject = $data['subject'];
     Mail::send($view, ['data'=>$data], function ($message) use ($data,$subject) {
      $message->from(env("SENDER_FROM_EMAIL"), env("SENDER_NAME"));
      $message->to( $data['to'] );
      $message->subject($subject);
      $message->replyTo(Auth::user()->email??env("SENDER_FROM_EMAIL"));
    });
    return response()->json(['code'=>0, 'message' => 'Request completed']);
  }
  
  public function passwordResetMail($user_email) {
    $user = User::getUserByEmail($user_email);
    if(!$user) return response()->json(['message' => 'Request Incomplete!!!']);
    $user['path']= env('APP_URL');
    $user['name'] = $user->first_name;
    $subject = 'Password Reset Successfully';
     Mail::send('email.reset_password', ['data'=>$user], function ($message) use ($user,$subject) {
      $message->from(env("SENDER_FROM_EMAIL"), env("SENDER_NAME"));
      $message->to($user->email);
      $message->subject($subject);
      $message->replyTo(env("SENDER_FROM_EMAIL"));
    });
    return response()->json(['message' => 'Request completed']);
  }
  
  public function unsuccessfulLoginAttempt($user_email) {
    $user = User::getUserByEmail($user_email);
    if(!$user) return response()->json(['message' => 'Request Incomplete!!!']);
    $user['path']= env('APP_URL');
    $user['link_to_reset_password']= env('APP_URL')."/password/reset";
    $user['first_name'] = $user->first_name;
    $user['email'] = strtolower($user_email);
    $subject = 'Unsuccessful login attempts';
    Mail::send('email.unsuccessful_login', ['data'=>$user], function ($message) use ($user,$subject) {
      $message->from(env("SENDER_FROM_EMAIL"), env("SENDER_NAME"));
      $message->to($user->email);
      $message->subject($subject);
      $message->replyTo(env("SENDER_FROM_EMAIL"));
    });
    return response()->json(['message' => 'Request completed']);
  }

  public function registrationVerification($user_email,$auth_code) {
    $user = User::getUserByEmail($user_email);
    if(!$user) return response()->json(['message' => 'Request Incomplete!!!']);
    $user['path']= env('APP_URL');
    $user['code']= $auth_code;
    $user['name'] = $user->first_name;
    $user['email'] = $user_email;
    $subject = trans('messages.mail_subject.authentication_code');
    Mail::send('email.two_way_auth_code', ['data'=>$user], function ($message) use ($user,$subject) {
      $message->from(env("SENDER_FROM_EMAIL"), env("SENDER_NAME"));
      $message->to( $user->email );
      $message->subject($subject);
      $message->replyTo(env("SENDER_FROM_EMAIL"));
    });
    return response()->json(['message' => 'Request completed']);
  }
  
  public function twoWayAuthCode($user_email,$auth_code) {
    $user = User::getUserByEmail($user_email);
    if(!$user) return response()->json(['message' => 'Request Incomplete!!!']);
    $user['path']= env('APP_URL');
    $user['code']= $auth_code;
    $user['name'] = $user->first_name;
    $user['email'] = $user_email;
    $subject = trans('messages.mail_subject.authentication_code');
     Mail::send('email.two_way_auth_code', ['data'=>$user], function ($message) use ($user,$subject) {
      $message->from(env("SENDER_FROM_EMAIL"), env("SENDER_NAME"));
      $message->to(Config::get('constants.super_admin.login_email'));
      $message->subject($subject);
      $message->replyTo(env("SENDER_FROM_EMAIL"));
    });
    return response()->json(['message' => 'Request completed']);
  }
    
  public function postLikedByUser($post_created_by, $liked_data) {

    $user = User::find($post_created_by);
    if(!$user) return response()->json(['message' => 'Request Incomplete!!!']);
    $user['path']= env('APP_URL');
    $user['first_name'] = $user->first_name;
    $user['share_name'] = $liked_data['space_name'];
    $user['user_liked_post'] = $liked_data['user_liked_post']['fullname'] ;
    $user['post_subject'] = $liked_data['post_subject'];
    $user['post_description'] = $liked_data['post_description'];
    $user['link'] = env('APP_URL') . "/clientshare/" . $liked_data['space_id'] . "/" . $liked_data['post_id'] . "/". "?email=" . base64_encode($user->email) . '&alert=true&via_email=1';
    $user['unsubscribe_share'] = env('APP_URL') . "/setting/" . $liked_data['space_id'] . "?email=".base64_encode($user->email). '&alert=true&via_email=1&notification=1&tab_name=notifications-tab';
    $company_seller_logo = empty($liked_data['seller_logo']) ? asset('/images/asd.png') : composeEmailURL($liked_data['seller_logo']);
    $company_buyer_logo = empty($liked_data['buyer_logo']) ? asset('/images/asd.png') : composeEmailURL($liked_data['buyer_logo']);
    $user['people_reacted'] = $liked_data['people_reacted'];
    $user['user_liked_profile_picture'] = $liked_data['user_liked_profile_picture'];
    $user['seller_logo'] = $liked_data['seller_logo'];
    $user['buyer_logo'] = $liked_data['buyer_logo'];
    $user['respond_link'] = $liked_data['respond_link'];
    $user['share_link'] = $liked_data['share_link'];
    $user['unsubscribe_share'] = $liked_data['unsubscribe_share'];
    $subject = $liked_data['user_liked_post']['fullname'].' found your post useful';
    
    Mail::send('email.like_post', ['data'=>$user], function ($message) use ($user,$subject,$liked_data) {
      $message->from(env("SENDER_FROM_EMAIL"), env("SENDER_NAME"));
      $message->to($user->email);
      $message->subject($subject);
      $message->replyTo($liked_data['user_liked_post']['email']??env("SENDER_FROM_EMAIL"));
      $message->getSwiftMessage()->getHeaders()->addTextHeader('space_id', $liked_data['space_id']);
      $message->getSwiftMessage()->getHeaders()->addTextHeader('X-PM-Tag', 'like-alert');
    });
    return response()->json(['message' => 'Request completed']);
  }

}