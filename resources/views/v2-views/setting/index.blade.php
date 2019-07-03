@extends('layouts.layoutsV2.master')
@section('styles')
<link rel="stylesheet" href="<?php echo e(url('css/sweetalert2(6.6.9).min.css')); ?>">
@endsection
@section('content')
@php
$is_admin = false;
if($user_setting['user_type_id'] == Config::get('constants.USER_ROLE_ID') ){
$is_admin = true;
}
@endphp
@if(Session::has('message'))
<div class="alert alert-info text-center">  
   {{Session::get('message')}}
</div>
@endif
@php
$spaceIdd = Session::get('space_info')['id'];
@endphp
<input type="hidden" value="{{$spaceIdd}}" class="hidden_space_id">
<div class="show_tab" showtab="{{$show_tab}}"></div>
<div class="container-fluid">
   <div class="main-container" id="settings">
   <div class="settings-page">
      <div class="settings-tabs-wrap">
         <div class="box">
          <div class="settings-mobile-header-wrap">
           <a class="settings-home-back-btn" href="{{url('/')}}"><img src="{{asset('images/v2-images/white_arrow.svg')}}" alt="Back" /></a>
           Settings
          </div>
            <ul class="nav nav-tabs setting_tabs" role="tablist">
               @if($is_admin)
               @if($domain_management['domain_restriction'])
               <li role="presentation" class="nav-item"><a href="#domain-management-tab" class="nav-link setting-tab" aria-controls="home" role="tab" data-toggle="tab" aria-selected="true">Domain management</a></li>
               @endif
               <li role="presentation" class="nav-item"><a href="#user-management-tab"  aria-controls="profile" role="tab" data-toggle="tab" class="nav-link user_manage setting-tab" aria-selected="false">User management</a></li>
              @endif
               @if(!Session::get('space_info')['invite_permission'] || $user_setting['user_type_id'] == Config::get('constants.USER_ROLE_ID'))
               <li role="presentation" class="{{ !$is_admin?'active':''}} nav-item"><a id="temp_trigger" class="nav-link setting-tab" href="#pending-invites-tab" aria-controls="messages" role="tab" data-toggle="tab" aria-selected="false">Pending invites</a></li>
               @endif
               <li role="presentation" class="nav-item"><a href="#notifications-tab" class="nav-link setting-tab notifications-tab" aria-controls="notifications" role="tab" data-toggle="tab" aria-selected="false">Email notifications</a></li>
               <li role="presentation" class="nav-item"><a href="#password-tab" class="nav-link setting-tab" aria-controls="settings" role="tab" data-toggle="tab" aria-selected="false">Account</a></li>
               @if($is_admin)
                  <li role="presentation" class="nav-item"><a href="#permissions-tab" class="nav-link setting-tab" aria-controls="settings" role="tab" data-toggle="tab" aria-selected="false">Permissions</a></li>
                  <li role="presentation" class="nav-item"><a href="#bulk-invitation-tab" class="nav-link setting-tab" aria-controls="settings" role="tab" data-toggle="tab" aria-selected="false">Bulk Invitation</a></li>
                  <li role="presentation" class="nav-item" id="bulk_add_users" style="display:none;"><a href="#bulk-add-users" class="nav-link setting-tab"  aria-controls="settings" role="tab" data-toggle="tab" aria-selected="false">Add User to new share</a></li>
                  <li role="presentation" class="nav-item"><a href="#power-bi-tab" class="nav-link setting-tab" aria-controls="settings" role="tab" data-toggle="tab" aria-selected="false">Power BI</a></li>
               @endif
            </ul>
         </div>
      </div>
      <div class="settings-content-wrap pending-invites">
         <div class="box">
            <div class="tab-content">
            <div class="settings-mobile-back-btn"><img src="{{asset('images/v2-images/white_arrow.svg')}}" alt="Back" /></div>
               @if($is_admin)
               <div role="tabpanel" class="tab-pane fade" id="domain-management-tab">
                  @include('v2-views.setting.domain_management')
               </div>
               <div role="tabpanel" class="tab-pane fade" id="user-management-tab"></div>
               @endif
               <div role="tabpanel" class="tab-pane fade {{ !$is_admin && !Session::get('space_info')['invite_permission']?'active':''}}" id="pending-invites-tab">
                  
               </div>
               <div role="tabpanel" class="tab-pane fade {{ !$is_admin && Session::get('space_info')['invite_permission']?'active':''}}" id="notifications-tab">
                  <div class="heading-wrap">
                     <h2 class="title">Email notifications</h2>
                  </div>
                  <div class="heading-wrap-mobile">
                     <h2 class="title">Email notifications</h2>
                  </div>
                  <div class="tab-inner-content notifications-inner-content white-bg-box">
                  <div class="form-inner-wrap">
                     <div class="alert alert-info text-center email_noti_msg" style="display:none"> Email notification settings saved </div>
                     <p>Send me an email when I am:</p>
                     <input type="hidden" class="spid" value="{{Session::get('space_info')['id']}}" userid="{{Auth::user()->id}}">
                     <form method="post" action="" name="email_notification_setting">
                        
                        <input type="hidden" name="space_id" value="{{Session::get('space_info')['id']}}">
                        <input type="hidden" name="user_id" value="{{Auth::user()->id}}">

                        <div class="form-group custom-checkbox-group">
                           <label for="post">Alerted to a post
                           <input id="post" name='space_user[post_alert]' class="post_check_box" type="checkbox" value="{{$user_setting['post_alert']}}" @if($user_setting['post_alert']) checked @endif>
                           <span class="custom-checkmark"></span>
                           </label>
                        </div>
                        <div class="form-group custom-checkbox-group">
                           <label for="comment">Alerted to a comment
                           <input id="comment" name='space_user[comment_alert]' class="comment_check_box" type="checkbox" value="{{$user_setting['comment_alert']}}" @if($user_setting['comment_alert']) checked @endif>
                           <span class="custom-checkmark"></span>
                           </label>
                        </div>
                        <div class="form-group custom-checkbox-group">
                           <label for="invite">Alerted to an accepted invitation
                           <input id="invite" name='space_user[invite_alert]' class="invite_check_box" type="checkbox" value="{{$user_setting['invite_alert']}}" @if($user_setting['invite_alert']) checked @endif>
                           <span class="custom-checkmark"></span>
                           </label>
                        </div>
                        <div class="form-group custom-checkbox-group">
                           <label for="like">Alerted to found my post useful
                           <input id="like" name='space_user[like_alert]' class="like_check_box" type="checkbox" value="{{$user_setting['like_alert']}}" @if($user_setting['like_alert']) checked @endif>
                           <span class="custom-checkmark"></span>
                           </label>
                        </div>
<!--  -->
                        <div class="form-group custom-checkbox-group">
                           <label for="tag_user_alert">Tagged in a comment
                           <input id="tag_user_alert" name='space_user[tag_user_alert]' class='tag_user_alert' type="checkbox" value="{{$user_setting['tag_user_alert']}}" @if($user_setting['tag_user_alert']) checked @endif>
                           <span class="custom-checkmark"></span>
                           </label>
                        </div>
<!--  -->
                        <p class="weekly-summary">Send me a weekly summary of the activity from all my Client Share's:</p>
                        
                        <div class="form-group custom-checkbox-group">
                           <label for="weekly">Weekly Client Share(s) summary
                           <input id="weekly" name='weekly_check_box' class="weekly_check_box" type="checkbox" value="{{$user_setting['weekly_alert']}}" @if($user_setting['weekly_alert']) checked @endif>
                           <span class="custom-checkmark"></span>
                           </label>
                        </div>
                        <div class="btn-group"><button class="btn btn-primary left save_notification_email_status" href="">Save</button></div>
                     </form>
                  </div>
                  </div>
               </div>
               <!-- Permission tab start  -->
               <div role="tabpanel" class="tab-pane fade" id="permissions-tab">
                  <div class="heading-wrap">
                     <h2 class="title">Permissions</h2>
                  </div>
                  <div class="heading-wrap-mobile">
                     <h2 class="title">Permissions</h2>
                  </div>
                  <div class="tab-inner-content permissions-inner-content white-bg-box">
                     <div class="alert alert-info text-center permission-settings-alert " style="display:none"> Permission settings saved </div>
                     <p>The following companies can post to Client Share: </p>
                     <input type="hidden" class="spid" value="{{Session::get('space_info')['id']}}" userid="{{Auth::user()->id}}">
                     <form id="user_permissions">
                        {{csrf_field()}}
                        <div class="form-group custom-checkbox-group">
                           <label for="post1">{{ Session::get('space_info')['SellerName']['company_name'] }}
                            <input id="post1" class="post_check_box post_permission" type="checkbox" value="" name="seller" {{ Session::get('space_info')['allow_seller_post']?'checked':'' }}>
                            <span class="custom-checkmark"></span>
                           </label>
                        </div>
                        <div class="form-group custom-checkbox-group">
                           <label for="comment1">{{ Session::get('space_info')['BuyerName']['company_name'] }}
                            <input id="comment1" class="comment_check_box post_permission" type="checkbox" name="buyer" {{ Session::get('space_info')['allow_buyer_post']?'checked':'' }}>
                            <span class="custom-checkmark"></span>
                           </label>
                        </div>
                           <p class="restricted-invitation">Restricted invitations:</p>
                           <div class="form-group custom-checkbox-group">
                           <label for="invite_permission">Only admins can invite new members to this share
                            <input id="invite_permission" class="post_check_box post_permission" type="checkbox" value="" name="invite_permission" {{ Session::get('space_info')['invite_permission']?'checked':'' }}>
                            <span class="custom-checkmark"></span>
                           </label>
                        </div>
                        <input type="hidden" name="space_id" value="{{Session::get('space_info')['id']}}">
                        <input type="hidden" name="buyer_id" value="{{Session::get('space_info')['BuyerName']['id']}}">
                        <input type="hidden" name="seller_id" value="{{Session::get('space_info')['SellerName']['id']}}">
                        <div class="btn-group"><button id="submit_permissions" class="btn btn-primary left"  disabled>Save</button></div>
                     </form>
                  </div>
               </div>
               <!-- Permission tab end -->
               <!-- bulk-invitation tab start  -->
               <div role="tabpanel" class="tab-pane fade" id="bulk-invitation-tab">
                  <div class="heading-wrap">
                     <h2 class="title">Bulk Invitation</h2>
                  </div>
                  <div class="heading-wrap-mobile">
                     <h2 class="title">Bulk Invitation</h2>
                  </div>
                  <div class="tab-inner-content white-bg-box bulk-invite-inner-content">
                     <div class="alert alert-info text-center email_noti_msg" style="display:none"> Email notification settings saved </div>
                     @if(Session::has('bulk_status_msg') )
                        <div class="alert alert-info alert-dismissable session-flash-message">{{Session::get('bulk_status_msg')}}<button type="button" class="close"><span><img src="{{asset('images/v2-images/close-icon.svg')}}" alt="close"></span></button></div>
                     @endif   
                     <p>To send a batch of invitations, please upload a CSV file with the following 3 columns: <span>first_name, last_name</span> and <span>email</span>.</p> 
                     <p class="info-text"><img src="{{asset('images/v2-images/info-icon.svg')}}" alt="Email Invitation Information" /> If more/different columns are present in the file then the file will not be accepted. The invitee details can then be added as rows in these columns. Please ensure there are no blank rows/cells and remove any unused rows from the bottom of the CSV or the file will not be accepted.</p>
                     <input type="hidden" class="spid" value="{{Session::get('space_info')['id']}}" userid="{{Auth::user()->id}}">
                     <form class="change_password_form bulk-invitation-form" action="send_invitations" method="POST" >
                        {{csrf_field()}}
                        <div class="input-group description-area">
                        <div class="file-upload-wrap">   
                          <button type="button" class="title attach-file-btn btn primary-btn"  onclick="invitationFileTrigger();"><span><img src="{{asset('images/v2-images/upload-icon.svg')}}" alt="Upload CSV" /></span>Upload CSV</button>
                        </div>
                           <div class="bulk-invitation-progress-info"></div>
                            <div class="bulk-checkbox-wrap"> 
                              <div class="custom-radiobtn-group">
                                 <label for="bulk-invite-mail">Send email invite via Client Share
                                    <input type="radio" name="user_invite" checked id="bulk-invite-mail" value="invite-email">
                                    <span class="custom-radiobtn"></span>
                                 </label>
                              </div>
                              <div class="custom-radiobtn-group">
                                 <label for="bulk-invite-export">Generate invite URLs for Export
                                    <input type="radio" name="user_invite" id="bulk-invite-export" value="invite-export">
                                    <span class="custom-radiobtn"></span>
                                 </label>
                              </div>
                            </div>
                            
                        <div class="mail-content">  
                        <p class="description-text">You can edit the greeting and message for all invitations here:</p>
                        <div class="mail-content-inner">
                           <span class="user-name">Hello First_name</span>
                           <input name="mail[body][]" type="hidden"/>
                           <input name="mail[body][]" type="hidden"/>
                           <textarea name="mail[body][]" class="form-control" colom="50" rows="4">I am inviting you to join me on this Client Share which has been set-up to share key information with you. The site is personalised, mobile, easy to share with colleagues and simple to use. It's a great way to ensure you have secure access to the latest updates and content at anytime, anywhere. Feel free to invite colleagues to join via the Client Share community.</textarea>
                           
                           <p>Thanks,</p>
                           <p>The {{ Session::get('space_info')['share_name']}} Client Share</p>
                           <p> On behalf of {{ ucfirst(Auth::user()->first_name) }} {{ ucfirst(Auth::user()->last_name) }}</p>

                           <input name="mail[body][]" value="Thanks" type="hidden"/>
                           <input name="mail[body][]" value="{{ Session::get('space_info')['share_name']}} Client Share" type="hidden"/>
                           <input name="mail[body][]" value="" type="hidden"/>
                          </div>
                          </div>
                        </div>
                        <div class="btn-group">  
                        <button class="btn btn-primary left bulk-email-trigger bulk-invite-mail" href="" disabled>Send Invitations</button>
                        <button class="btn btn-primary left bulk-email-trigger bulk-invite-export" href="" disabled style="display:none;">Generate invite URLs</button>
                        </div>
                        <input type="hidden" name='bulk_invitation_file' class='bulk-invitation-file'>
                        <input type="hidden" name='share_id' value="{{Session::get('space_info')['id']}}">
                        <input type="hidden" name='mail[subject]' value="{{ ucfirst(Auth::user()->first_name) }} {{ ucfirst(Auth::user()->last_name) }} is inviting you to the {{ $space_data->share_name }} Client Share">
                        <input type="hidden" name='finalized_data'>
                     </form>
                  </div>
               </div>

               <!-- Power-BI tab start  -->
               <div role="tabpanel" class="tab-pane fade" id="power-bi-tab">
                  <div class="heading-wrap">
                     <h2 class="title">Power BI</h2>
                     <button class="btn btn-secondary power-bi-trigger" type="button" data-toggle="modal" data-target="#power-bi-modal"><span><img src="{{asset('images/v2-images/add_small_icon.svg')}}" alt="Add report" /></span>Add report</button>
                  </div>
                  <div class="heading-wrap-mobile">
                     <h2 class="title">Power BI</h2>
                  </div>
                  <div class="tab-inner-content user-management-inner-content powerBI-content-inner">
                     <div class="alert alert-info text-center email_noti_msg" style="display:none"> Email notification settings saved </div>
                     <div>
                        <div class="">
                           <div class="form_field_section bi-table">

                              <div class="tablerow tablehdrow">
                                <div class="tablecell number-wrap"><span class="approved-small-text">#</span></div>
                                <div class="tablecell report-name-wrap"><span class="approved-small-text">Report Name</span></div>
                                <div class="tablecell report-type-wrap"><span class="approved-small-text">Type</span></div>
                                <div class="tablecell createdon-wrap"><span class="approved-small-text">Created on</span></div>
                                <div class="tablecell action-wrap"><span class="approved-small-text">Action</span></div>
                              </div>
                              <div class="report_block"></div>

                           </div>
                        </div>                         
                        <div class="hidden no-data-wrap">No reports added yet</div>
                     </div>
                  </div>
               </div>
               <!-- Power-BI tab end -->

              
                <div role="tabpanel" class="tab-pane fade" id="bulk-add-users">
                  <div class="heading-wrap">
                     <h4 class="title">SCRIPT to map existing users to new share</h4>
                  </div>
                  <div class="tab-inner-content">                    
                     @if(Session::has('bulk_status_msg') )
                        <div class="alert alert-info alert-dismissable session-flash-message">{{Session::get('bulk_status_msg')}}<button type="button" class="close"><span>×</span></button></div>
                     @endif               
                     <input type="hidden" class="spid" value="{{Session::get('space_info')['id']}}" userid="{{Auth::user()->id}}">
                     <form class="change_password_form bulk-invitation-form" action="useraddshare" method="POST" enctype="multipart/form-data">
                        {{csrf_field()}}
                        <div class="col-lg-8 col-md-8 col-sm-10 col-xs-12 input-group description-area">
                           <input type="text" class='invitation-file' name="email" value="" placeholder="Invited by email">
                           <div class="bulk-invitation-progress-info"></div>                            
                       
                        </div>
                        <div class="col-lg-8 col-md-8 col-sm-10 col-xs-12 input-group description-area">
                           <input type="file" class='invitation-file' name="user_list" value="Upload CSV">
                           <div class="bulk-invitation-progress-info"></div>                            
                       
                        </div>
                          
                        <button class="btn btn-primary left bulk-email-trigger " href="" >Upload</button>
                        
                        <input type="hidden" name='bulk_invitation_file' class='bulk-invitation-file'>
                        <input type="hidden" name='share_id' value="{{Session::get('space_info')['id']}}">
                        <input type="hidden" name='mail[subject]' value="{{ ucfirst(Auth::user()->first_name) }} {{ ucfirst(Auth::user()->last_name) }} is inviting you to the {{ $space_data->share_name }} Client Share">
                        <input type="hidden" name='finalized_data'>
                     </form>
                  </div>
               </div>
                         
               <!-- bulk-invitation tab end -->
               <div role="tabpanel" class="tab-pane fade" id="password-tab">
                  <div class="heading-wrap">
                     <h2 class="title">Account</h2>
                  </div>
                  <div class="heading-wrap-mobile">
                     <h2 class="title">Account</h2>
                  </div>
                  <div class="tab-inner-content white-bg-box account-inner-content">
                  <div class="form-inner-wrap">
                     <div class="alert alert-info text-center changepasswordalert" style="display:none;"></div>
                     <p>Change your password here:</p>
                     <form class="change_password_form" action="" method="POST">
                        {{csrf_field()}}
                        <div class="form_field_section">
                           <div class="input-group form-group">
                              <label>Current password</label>
                              <input type="password" class="form-control no-margin current_pass setting-pass-update" placeholder="Type your password" name="current_password" value="" autocomplete="off">
                              <span class="left current_pass_error pass-error"></span>
                           </div>
                           <div class="input-group form-group">
                              <label>New password</label>
                              <input type="password" class="form-control no-margin new_pass valid setting-pass-update" placeholder="Type your new password" name="new_password" value="" autocomplete="off">
                              <span class="left new_pass_error pass-error"></span>
                           </div>
                           <div class="input-group form-group">
                              <label>Verify password</label>
                              <input type="password" class="form-control no-margin new_confirm_pass valid setting-pass-update" placeholder="Verify your password" name="new_confirm_password" value="" autocomplete="off">
                              <span class="left new_confirm_pass_error pass-error"></span>
                           </div> 
                        </div>
                        <div class="btn-group"><button class="btn btn-primary left save_password" href="">Save</button></div>
                     </form>
                     </div>
                     <div class="account-disable-wrap">
                     <div class="account-disable"><p>Disable account:</p><a href="#" data-toggle="modal" data-target="#disableaccount">Disable my <span>{{ Session::get('space_info')['share_name'] }}</span> account</a></div>
                     </div>
                     <div class="modal fade in disable-account-pop sm-popup" id="disableaccount" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
                        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                           <div class="modal-content promot-user">
                              <div class="modal-header">
                                 <h5 class="modal-title" id="myModalLabel">Disable account</h5>
                                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true"><img src="{{asset('images/v2-images/close-icon.svg')}}" alt="close"></span>
                                 </button>
                              </div>
                              <div class="modal-body">
                                 <p>Are you sure you want to <span>disable your account</span> for this Client Share?</p>
                                 <p class="disable-account-info"> Your posts will remain on the feed but you will no longer be a member of the community, receive notifications or have access to this Client Share.</p>
                                 <div class="btn-group">
                                 <form action="{{ url('/disable_account',[],env('HTTPS_ENABLE', true)) }}" method="POST">
                                    {{csrf_field()}}
                                    <input type="hidden" name="sp_id" value="{{Session::get('space_info')['id']}}">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button class="btn btn-primary confirm">Disable account</button>
                                 </form>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- <div class="loading-icon text-center">
                  <div class="loading-flex">
                    <img class="setting_loading" src="{{ url('/',[], env('HTTPS_ENABLE', true)) }}/images/v2-images/loader.svg">
                  </div>
              </div> -->
            </div>
         </div>
      </div>
      </div>
   </div>
   <!-- col-md-8 -->

@include('v2-views/setting/modals')
@include('v2-views/setting/handlebar_components')

@endsection

@section('scripts')
<script src="{{ url('js/jquery.fileupload.js?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}"></script>
<script src="{{ url('js/jquery-ui.min.js?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}"></script>
<script src="{{ url('js/custom/setting_v2.js?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}"></script>
<script src="{{ url('js/setting_v2.js?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}"></script>
<script src="{{ url('js/custom/v2/invite.js?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
<script type="text/javascript" src="{{ url('theia-sticky-sidebar-master/dist/ResizeSensor.js',[],env('HTTPS_ENABLE', true)) }}"></script>
<script type="text/javascript" src="{{ url('theia-sticky-sidebar-master/dist/theia-sticky-sidebar.js',[],env('HTTPS_ENABLE', true)) }}"></script>
<script type="text/javascript" src="{{ url('theia-sticky-sidebar-master/js/test.js',[],env('HTTPS_ENABLE', true)) }}"></script>
<script>
var user_management = "{{ url('/user_management',[],env('HTTPS_ENABLE', true)) }}";
var pending_invites = "{{ url('/pending_invites',[],env('HTTPS_ENABLE', true)) }}";
</script>

<script  src="{{ url('js/custom/handlebarjs_helpers.js',[],env('HTTPS_ENABLE', true)) }}"></script>
<script src="{{ url('js/sweetalert2(6.6.9).min.js') }}"></script>
@endsection