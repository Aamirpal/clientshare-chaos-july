@extends(session()->get('layout'))
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
<script src="{{ url('js/custom/setting.js?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}"></script>
<style>
   #thanku-feedback{pointer-events: none;}
   #thanku-feedback .save-popup{pointer-events: auto;}
</style>
<div class="show_tab" showtab="{{$show_tab}}"></div>
<div class="container-fluid feed-content">
   <div class="col-lg-10 col-md-12 col-md-12 col-md-12 mid-content settings_page_content">
      <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 settings_tabs_wrap">
         <div class="box">
            <ul class="nav nav-tabs setting_tabs" role="tablist">
               @if($is_admin)
               @if($domain_management['domain_restriction'])
               <li role="presentation" class=""><a href="#domain-management-tab" class="side_tabs" aria-controls="home" role="tab" data-toggle="tab">Domain management</a></li>
               @endif
               <li role="presentation" class=""><a href="#user-management-tab"  aria-controls="profile" role="tab" data-toggle="tab" class="user_manage side_tabs">User management</a></li>
               <li role="presentation"><a href="#feedback-tab" aria-controls="notifications" role="tab" class="side_tabs" data-toggle="tab">Feedback</a></li>
               @endif
               @if(!Session::get('space_info')['invite_permission'] || $user_setting['user_type_id'] == Config::get('constants.USER_ROLE_ID'))
               <li role="presentation" class="{{ !$is_admin?'active':''}}"><a id="temp_trigger" class="side_tabs" href="#pending-invites-tab" aria-controls="messages" role="tab" data-toggle="tab">Pending invites</a></li>
               @endif
               <li role="presentation"><a href="#notifications-tab" class="side_tabs notifications-tab" aria-controls="notifications" role="tab" data-toggle="tab">Email notifications</a></li>
               <li role="presentation"><a href="#password-tab" class="side_tabs" aria-controls="settings" role="tab" data-toggle="tab">Account</a></li>
               @if($is_admin)
                  <li role="presentation"><a href="#permissions-tab" class="side_tabs" aria-controls="settings" role="tab" data-toggle="tab">Permissions</a></li>
                  <li role="presentation"><a href="#bulk-invitation-tab" class="side_tabs" aria-controls="settings" role="tab" data-toggle="tab">Bulk Invitation</a></li>
                  <li role="presentation" id="bulk_add_users" style="display:none;"><a href="#bulk-add-users" class="side_tabs"  aria-controls="settings" role="tab" data-toggle="tab">Add User to new share</a></li>
                  <li role="presentation"><a href="#power-bi-tab" class="side_tabs" aria-controls="settings" role="tab" data-toggle="tab">Power BI</a></li>
               @endif
            </ul>
         </div>
      </div>
      <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 settings_content_wrap pending-invites">
         <div class="box">
            <div class="tab-content">
               @if($is_admin)
               <div role="tabpanel" class="tab-pane" id="domain-management-tab">
                  <div class="heading_wrap">
                     <h4 class="title">Domain management</h4>
                     <button class="invite-btn add_domain_row" type="button">ADD EMAIL DOMAIN</button>
                  </div>
                  <div class="tab-inner-content">
                     <p>Email invitation’s cannot be sent to email addresses outside the approved domain(s).</p>
                     <span style="display:none" class="success-msg white_box_info">Restricted email access to domains: </span>
                     <form class="domain_management_form set_email_rule" action="/domain_update" method="post" autocomplete="off">
                        <div class="form_field_section">
                           <div class="input-group">
                              <span class="approved-small-text">Approved email domains</span>
                           </div>
                           @if( isset($domain_management['metadata']['rule']) )
                           @foreach($domain_management['metadata']['rule'] as $rule)
                           <div class="input-group domain-input-grp">
                              <span class="input-group-addon" id="basic-addon1">@</span>
                              <input type="text" class="form-control domain_name_inp" placeholder="IBM.com" name="rule" autocomplete="off" value="{{ $rule['value'] }}" disabled>
                              <div class="dropdown hover-dropdown">
                                 <a href="#" class="dropdown-toggle  dots" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                 <span></span>
                                 </a>
                                 <ul class="dropdown-menu">
                                    <li class="domain_inp_edit"><a href="#!">Edit domain</a></li>
                                    <li class="domain_inp_delete"><a href="#!" class="delete-link">Delete domain</a></li>
                                 </ul>
                              </div>
                           </div>
                           @endforeach
                           @endif
                           <div class="link-wrap input-group">
                              <a class="link add_domain_row" href="#!">Add email domain</a>
                           </div>
                        </div>
                        <input type="hidden" class="spaceid" value="{{Session::get('space_info')['id']}}" userid="{{Auth::user()->id}}">
                        <button type="button" class="btn btn-primary save-last" onclick="set_email_rule_in_setting('domain_management_form');">Save</button>
                     </form>
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane" id="user-management-tab">
                  
               </div>
               @endif
               <div role="tabpanel" class="tab-pane {{ !$is_admin && !Session::get('space_info')['invite_permission']?'active':''}}" id="pending-invites-tab">
                  
               </div>
               <div role="tabpanel" class="tab-pane {{ !$is_admin && Session::get('space_info')['invite_permission']?'active':''}}" id="notifications-tab">
                  <div class="heading_wrap">
                     <h4 class="title">Email notifications</h4>
                  </div>
                  <div class="tab-inner-content">
                     <div class="alert alert-info text-center email_noti_msg" style="display:none"> Email notification settings saved </div>
                     <p>Send me an email when I am:</p>
                     <input type="hidden" class="spid" value="{{Session::get('space_info')['id']}}" userid="{{Auth::user()->id}}">
                     <form method="post" action="" name="email_notification_setting">
                        
                        <input type="hidden" name="space_id" value="{{Session::get('space_info')['id']}}">
                        <input type="hidden" name="user_id" value="{{Auth::user()->id}}">

                        <div class="form-group"><input id="post" name='space_user[post_alert]' class="post_check_box" type="checkbox" value="{{$user_setting['post_alert']}}" @if($user_setting['post_alert']) checked @endif>
                           <label for="post">Alerted to a post</label>
                        </div>
                        <div class="form-group"><input id="comment" name='space_user[comment_alert]' class="comment_check_box" type="checkbox" value="{{$user_setting['comment_alert']}}" @if($user_setting['comment_alert']) checked @endif>
                           <label for="comment">Alerted to a comment</label>
                        </div>
                        <div class="form-group"><input id="like" name='space_user[like_alert]' class="like_check_box" type="checkbox" value="{{$user_setting['like_alert']}}" @if($user_setting['like_alert']) checked @endif>
                           <label for="like">Alerted to a like on my post</label>
                        </div>
                        <div class="form-group"><input id="invite" name='space_user[invite_alert]' class="invite_check_box" type="checkbox" value="{{$user_setting['invite_alert']}}" @if($user_setting['invite_alert']) checked @endif>
                           <label for="invite">Alerted to an accepted invitation</label>
                        </div>
<!--  -->
                        <div class="form-group">
                           <input id="tag_user_alert" name='space_user[tag_user_alert]' class='tag_user_alert' type="checkbox" value="{{$user_setting['tag_user_alert']}}" @if($user_setting['tag_user_alert']) checked @endif>
                           <label for="tag_user_alert">Tagged in a comment</label>
                        </div>
<!--  -->
                        <p>Send me a weekly summary of the activity from all my Client Share's:</p>
                        
                        <div class="form-group"><input id="weekly" name='weekly_check_box' class="weekly_check_box" type="checkbox" value="{{$user_setting['weekly_alert']}}" @if($user_setting['weekly_alert']) checked @endif>
                           <label for="weekly">Weekly Client Share(s) summary</label>
                        </div>
                     </form>
                     <button class="btn btn-primary left save_notification_email_status" href="">Save</button>
                  </div>
               </div>
               <!-- Permission tab start  -->
               <div role="tabpanel" class="tab-pane" id="permissions-tab">
                  <div class="heading_wrap">
                     <h4 class="title">Permissions</h4>
                  </div>
                  <div class="tab-inner-content">
                     <div class="alert alert-info text-center email_noti_msg" style="display:none"> Email notification settings saved </div>
                     <p>The following companies can post to Client Share: </p>
                     <input type="hidden" class="spid" value="{{Session::get('space_info')['id']}}" userid="{{Auth::user()->id}}">
                     <form method="post" action="../allow_posting">
                        {{csrf_field()}}
                        <div class="form-group">
                           <input id="post1" class="post_check_box post_permission" type="checkbox" value="" name="seller" {{ Session::get('space_info')['allow_seller_post']?'checked':'' }}>
                           <label for="post1">{{ Session::get('space_info')['SellerName']['company_name'] }}</label>
                        </div>
                        <div class="form-group">
                           <input id="comment1" class="comment_check_box post_permission" type="checkbox" name="buyer" {{ Session::get('space_info')['allow_buyer_post']?'checked':'' }}>
                           <label for="comment1">{{ Session::get('space_info')['BuyerName']['company_name'] }}</label>
                        </div>
                           <p>Restricted invitations</p>
                           <div class="form-group">
                           <input id="invite_permission" class="post_check_box post_permission" type="checkbox" value="" name="invite_permission" {{ Session::get('space_info')['invite_permission']?'checked':'' }}>
                           <label for="invite_permission">Only admins can invite new members to this share</label>
                        </div>
                        <input type="hidden" name="space_id" value="{{Session::get('space_info')['id']}}">
                        <input type="hidden" name="buyer_id" value="{{Session::get('space_info')['BuyerName']['id']}}">
                        <input type="hidden" name="seller_id" value="{{Session::get('space_info')['SellerName']['id']}}">
                        <button class="btn btn-primary left" href="" disabled>Save</button>
                     </form>
                  </div>
               </div>
               <!-- Permission tab end -->
               <!-- bulk-invitation tab start  -->
               <div role="tabpanel" class="tab-pane" id="bulk-invitation-tab">
                  <div class="heading_wrap">
                     <h4 class="title">Bulk Invitation</h4>
                  </div>
                  <div class="tab-inner-content">
                     <div class="alert alert-info text-center email_noti_msg" style="display:none"> Email notification settings saved </div>
                     @if(Session::has('bulk_status_msg') )
                        <div class="alert alert-info alert-dismissable session-flash-message">{{Session::get('bulk_status_msg')}}<button type="button" class="close"><span>×</span></button></div>
                     @endif   
                     <p>To send a batch of invitations, please upload a CSV file with the following 3 columns: <b>first_name, last_name</b> and <b>email</b>. <br>If more/different columns are present in the file then the file will not be accepted. The invitee details can then be added as rows in these columns. <br>Please ensure there are no blank rows/cells and remove any unused rows from the bottom of the CSV or the file will not be accepted.</p>
                     <input type="hidden" class="spid" value="{{Session::get('space_info')['id']}}" userid="{{Auth::user()->id}}">
                     <form class="change_password_form bulk-invitation-form" action="send_invitations" method="POST" >
                        {{csrf_field()}}
                        <div class="col-lg-8 col-md-8 col-sm-10 col-xs-12 input-group description-area">
                           <button type="button" class="title attach-file-btn btn primary-btn" onclick="invitationFileTrigger();" >Upload CSV</button>
                           <input type="file" class='invitation-file' style="display:none">
                           <div class="bulk-invitation-progress-info"></div>
                            <div class="bulk-checkbox-wrap">
                              <div>
                                 <input type="radio" name="user_invite" checked id="bulk-invite-mail" value="invite-email">
                                 <label for="bulk-invite-mail">
                                    <span></span>
                                 Send email invite via Client Share</label>
                              </div>
                              <div>
                                 <input type="radio" name="user_invite" id="bulk-invite-export" value="invite-export">
                                 <label for="bulk-invite-export">
                                    <span></span>
                                 Generate invite URLs for Export</label>
                              </div>
                           </div>
                        <div class="mail-content">  
                            <p class="description-text">You can edit the greeting and message for all invitations here:</p>
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
                          
                        <button class="btn btn-primary left bulk-email-trigger bulk-invite-mail" href="" disabled>Send Invitations</button>
                        <button class="btn btn-primary left bulk-email-trigger bulk-invite-export" href="" disabled style="display:none;">Generate invite URLs</button>
                        <input type="hidden" name='bulk_invitation_file' class='bulk-invitation-file'>
                        <input type="hidden" name='share_id' value="{{Session::get('space_info')['id']}}">
                        <input type="hidden" name='mail[subject]' value="{{ ucfirst(Auth::user()->first_name) }} {{ ucfirst(Auth::user()->last_name) }} is inviting you to the {{ $space_data->share_name }} Client Share">
                        <input type="hidden" name='finalized_data'>
                     </form>
                  </div>
               </div>

               <!-- Power-BI tab start  -->
               <div role="tabpanel" class="tab-pane" id="power-bi-tab">
                  <div class="heading_wrap">
                     <h4 class="title">Power BI</h4>
                     <div class="title power-bi-trigger" data-toggle="modal" data-target="#power-bi-modal" style="float: right;"><i class="fa fa-line-chart" aria-hidden="true"></i> Add report</div>
                  </div>
                  <div class="tab-inner-content">
                     <div class="alert alert-info text-center email_noti_msg" style="display:none"> Email notification settings saved </div>
                     <div>
                        <div class="">
                           <h4 class="title">Power BI reports</h4>
                           <div class="form_field_section bi-table">
                              <div class="tablerow tablehdrow">
                                 <table class="table table-hover">
                                 <thead>
                                    <tr>
                                       <th scope="col">#</th>
                                       <th scope="col">Report Name</th>
                                       <th scope="col">Type</th>
                                       <th scope="col">Created on</th>
                                       <th scope="col">Action</th>
                                    </tr>
                                 </thead>
                                 <tbody class="report_block"></tbody>
                                 </table>
                              </div>
                           </div>
                        </div>                         
                        <div class="hidden">
                           <h4>No reports added yet</h4>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- Power-BI tab end -->

              
                <div role="tabpanel" class="tab-pane" id="bulk-add-users">
                  <div class="heading_wrap">
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
               <div role="tabpanel" class="tab-pane" id="feedback-tab">
                  <div class="heading_wrap">
                     <h4 class="title">Feedback</h4>
                  </div>
                  <div class="tab-inner-content">
                     <div class="alert alert-info text-center feedback-status-message" style="display:none"> Feedback Status settings saved </div>
                     <p class="feedback-text">The Feedback feature allows members of the Buyer community to give Feedback on the relationship each Quarter. At the start of each Quarter Buyers will have a 2 week window in which they can submit feedback on the previous Quarter, once the window has closed the feedback and NPS result for the previous quarter becomes available to all members of the Community.
                        <br><br>When Feedback is turned on, the month where the first window opens is selected. This allows the you choose when the Quarters fall for this relationship. The first window will open at the start of the selected month i.e. if you set Feedback to start in July then the first window will open on July 1st and Buyers will be giving feedback on the Quarter April-June.
                     </p>
                     <form method="post" action="" name="feedback_on_off_status_setting">
                        <input type="hidden"  value="{{Session::get('space_info')['id']}}" name="space_id" class="space_id">
                        <div class="form-group"><input id="feedback_on_off"  type="checkbox" value="" @if($feedback_status_to_date->feedback_status) checked @endif>
                           <label for="feedback_on_off" class="">Enable Feedback on this Client Share</label>
                        </div>
                     </form>
                     @if( $feedback_status['feedback_status'] )
                     <div class="feedback-reminder box {{ $feedback_status['current_month_eligible'] ?'':'disabled' }}">
                        <p class="disable-text">A reminder to send feedback will become available when the Feedback window is open.</p>
                        <p class="plain-text">{{$feedback_status['given_feedback']}}/{{$feedback_status['share_members']}} buyers have given feedback during this window.</br>Buyers have {{$feedback_status['days_left']}} days remaining in this window to give feedback.</p>
                        <button class="btn btn-primary disable-btn" disabled>Send reminder</button>
                        <button class="btn btn-primary plain-btn" id="reminder" data-toggle="modal" data-target="#feedback-reminder" {{$feedback_status['given_feedback']==$feedback_status['share_members'] || $space_data['feedback_reminder_status']['status']?'disabled':''}}>Send reminder</button>
                        @if($space_data['feedback_reminder_status']['status'] && $feedback_status['given_feedback']!=$feedback_status['share_members'])
                        <p>{{$space_data['feedback_reminder_status']['message']}}</p>
                        @endif
                     </div>
                     <!--feedback-reminder -->
                     @endif
                     @if( $feedback_status['feedback_status'] )
                     <button class="btn btn-primary left thankyou-feedback-button" disabled data-toggle="modal" data-target="#disable_feedback" >Save</button> 
                     @else
                     <button class="btn btn-primary left thankyou-feedback-button" disabled data-toggle="modal" data-target="#thanku-feedback" >Save</button>
                     @endif
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane" id="password-tab">
                  <div class="heading_wrap">
                     <h4 class="title">Account</h4>
                  </div>
                  <div class="tab-inner-content">
                     <div class="alert alert-info text-center changepasswordalert" style="display:none;"></div>
                     <p>Change your password here.</p>
                     <form class="change_password_form" action="" method="POST">
                        {{csrf_field()}}
                        <div class="form_field_section">
                           <div class="col-lg-8 col-md-8 col-sm-10 col-xs-12 input-group">
                              <label>Current password</label>
                              <input type="password" class="form-control no-margin current_pass" placeholder="Type your password here" name="current_password" value="">
                              <span class="left current_pass_error"></span>
                           </div>
                           <div class="col-lg-8 col-md-8 col-sm-10 col-xs-12 input-group">
                              <label>New password</label>
                              <input type="password" class="form-control no-margin new_pass valid" placeholder="Type your new password here" name="new_password" value="">
                              <span class="left new_pass_error"></span>
                           </div>
                           <div class="col-lg-8 col-md-8 col-sm-10 col-xs-12 input-group">
                              <label>Verify password</label>
                              <input type="password" class="form-control no-margin new_confirm_pass valid" placeholder="Verify your password here" name="new_confirm_password" value="">
                              <span class="left new_confirm_pass_error"></span>
                           </div>
                        </div>
                        <button class="btn btn-primary left save_password" href="">Save</button>
                     </form>
                     <span class="account-disable"><a href="#" data-toggle="modal" data-target="#disableaccount">Disable my {{ Session::get('space_info')['share_name'] }} account</a></span>
                     <div class="modal fade in disable-account-pop" id="disableaccount" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
                        <div class="modal-dialog modal-sm" role="document">
                           <div class="modal-content promot-user">
                              <div class="modal-header">
                                 <h4 class="modal-title" id="myModalLabel">Disable account</h4>
                              </div>
                              <div class="modal-body">
                                 <p>Are you sure you want to disable your account for this Client Share? Your posts will remain on the feed but you will no longer be a member of the community, receive notifications or have access to this Client Share.</p>
                              </div>
                              <div class="modal-footer">
                                 <form action="{{ url('/disable_account',[],env('HTTPS_ENABLE', true)) }}" method="POST">
                                    {{csrf_field()}}
                                    <input type="hidden" name="sp_id" value="{{Session::get('space_info')['id']}}">
                                    <button type="button" class="btn btn-default left" data-dismiss="modal">Cancel</button>
                                    <button class="btn btn-primary left confirm">Confirm</button>
                                 </form>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="loading-icon text-center">
            <div class="loading-flex">
               <img class="setting_loading" src="{{ url('/',[], env('HTTPS_ENABLE', true)) }}/images/puff.svg">
            </div>
         </div>
      </div>
   </div>
   <!-- col-md-8 -->

@include('setting/modals')
@include('setting/handlebar_components')
<script>
var user_management = "{{ url('/user_management',[],env('HTTPS_ENABLE', true)) }}";
var pending_invites = "{{ url('/pending_invites',[],env('HTTPS_ENABLE', true)) }}";
</script>

<script  src="{{ url('js/handle_bar.js',[],env('HTTPS_ENABLE', true)) }}"></script>
<script  src="{{ url('js/custom/handlebarjs_helpers.js',[],env('HTTPS_ENABLE', true)) }}"></script>
<script  src="{{ url('js/setting.js',[],env('HTTPS_ENABLE', true)) }}"></script>
@endsection