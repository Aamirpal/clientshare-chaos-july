<?php
   $clientsharname=Session::get('space_info')['share_name'];
   $clientsharid=Session::get('space_info')['id'];
   ?>
@extends(session()->get('layout'))
@section('content')
<script type="text/javascript">
   $(document).ready(function(){
      if( /Android|webOS|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) { 
        $(".helpicon").attr("data-placement", 'left');
      }
      
      if( /iPad/i.test(navigator.userAgent) ) { 
        $(".helpicon").attr("data-placement", 'bottom'); 
      }
   }); 
</script>
@php  $currentDate=date('Y');
   $check_year = request()->segment(3); 
   $check_month = request()->segment(2);    
   if(isset($check_year)){   
     $currentDate = $check_year;     
   } else{   
     $currentDate = $currentDate;    
   }   
   $space_info_value = Session::get('space_info');
   $user_count = sizeOfCustom($feedback) + sizeOfCustom($get_non_feedback_user);
@endphp
<div class="container-fluid feed-content feedback-compact">
   <div class="col-lg-10 col-md-12 col-md-12 col-md-12 mid-content settings_page_content">
      <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 settings_tabs_wrap ">
         <div class="box">
            @php 
            $startDate = Session::get('space_info')['created_at'];
            $startYear = date('Y',strtotime($startDate));
            $startMonth = date('n',strtotime($startDate));
            $smonth = date('n',strtotime($startDate));   
            $syear = date('Y',strtotime($startDate));
            @endphp
            <ul class="nav nav-tabs year-tab" role="tablist">
               <!-- check any feedback is given -->
               @if( sizeOfCustom($feedback_quaters) || $data['feedback_current_status']['give_feedback'] || $data['feedback_current_status']['display_feedback_tat'])
               <li class="active feedback-year">Feedback quarters<span class="down-month"><img src="{{env('APP_URL')}}/images/ic_arrow_drop_down.svg" alt=""></span></li>
               @else
               <li class="active feedback-year">Nothing to display. </li>
               @endif
               <!-- check any feedback is given end-->
               @foreach($feedback_quaters as $key => $val)
               
               @php
                  $val->feedback_month = json_decode($val->feedback_month,true);
                  $nav_quater = Carbon\Carbon::parse($val->feedback_month[0]['date'])->format('F Y')." - ".Carbon\Carbon::parse($val->feedback_month[sizeOfCustom($val->feedback_month)-1]['date'])->format('F Y');

                  $nav_active = strtolower($nav_quater) == strtolower($data['current_quater'])?'active':'';
               @endphp
               <li getmonthnum="1" class="month-class {{$nav_active}}" role="presentation">
                  <a href="{{env('APP_URL').'/feedback/'.Carbon\Carbon::parse($val->created_at)->month.'/'.Carbon\Carbon::parse($val->created_at)->year}}">{{Carbon\Carbon::parse($val->feedback_month[0]['date'])->format('F Y')}} - {{Carbon\Carbon::parse($val->feedback_month[sizeOfCustom($val->feedback_month)-1]['date'])->format('F Y')}}</a>
               </li>
               @endforeach
            </ul>
         </div>
      </div>
      <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 settings_content_wrap">
         <div class="box">
            <div class="tab-content">
               <div role="tabpanel" class="tab-pane active" id="feedback">
                  @php            
                  $dateObj   = DateTime::createFromFormat('!m', $month);
                  $monthName = $dateObj->format('F');
                  @endphp 
                  <div class="heading_wrap">
                     <h4 class="title">{{ $data['feedback_current_status']['seller_view']?'':$data['current_quater'] }}</h4>
                     <!--  FEEDBACK POPUP -->
                     <div id="feedback-popup" class="modal fade feedback-popup" role="dialog">
                        <div class="modal-dialog modal-lg">
                           <!-- Modal content-->
                           <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal"><img src="{{env('APP_URL')}}/images/ic_highlight_removegray.svg" alt=""></button>
                              <h4 class="modal-title">{{ $data['current_quater'] }}</h4>
                           </div>
                           <div class="modal-content">
                              <form method="post" action="{{ url('/saveFeedback',[],env('HTTPS_ENABLE', true)) }}" enctype="multipart/form-data" class="feedback_form">
                                 {!! csrf_field() !!}
                                 <input type="hidden" name="space_id" value="{{ Session::get('space_info')['id'] }}">
                                 <input type="hidden" name="user_id" value="{{Auth::user()->id}}">
                                 <div class="modal-body">
                                    <h1>Your opinion is important.</h1>
                                    <h2>We'd like to hear it.</h2>
                                    <p class="feedback-title">How likely are you to recommend {{$space_info_value['SellerName']['company_name']}} to a friend or colleague?<span style="color: #0d47a1;"> *<a class="helpicon recommend" data-toggle="popover" data-trigger="hover" title=""  data-placement="right" data-content="NPS is an industry standard customer loyalty metric. It is calculated based on one question with a ranking from 1-10. Your overall score ranges from –100 to +100; a positive NPS is deemed to be good, whilst a NPS of +50 is excellent."><i class="fa fa-question-circle"></i></a></p>
                                    <div class="rating-wrap">
                                       <span class="likely">Not at all likely</span>
                                       <div class="radio">
                                          <input id="r0" type="radio" name="rating" value="0" class="rating" >
                                          <label for="r0">0</label>
                                       </div>
                                       <div class="radio">
                                          <input id="r1" type="radio" name="rating" value="1" class="rating" >
                                          <label for="r1">1</label>
                                       </div>
                                       <div class="radio">
                                          <input id="r2" type="radio" name="rating" value="2"  class="rating" >
                                          <label for="r2">2</label>
                                       </div>
                                       <div class="radio">
                                          <input id="r3" type="radio" name="rating" value="3"  class="rating" >
                                          <label for="r3">3</label>
                                       </div>
                                       <div class="radio">
                                          <input id="r4" type="radio" name="rating" value="4"  class="rating" >
                                          <label for="r4">4</label>
                                       </div>
                                       <div class="radio">
                                          <input id="r5" type="radio" name="rating" value="5"  class="rating" >
                                          <label for="r5">5</label>
                                       </div>
                                       <div class="radio">
                                          <input id="r6" type="radio" name="rating" value="6"  class="rating" >
                                          <label for="r6">6</label>
                                       </div>
                                       <div class="radio">
                                          <input id="r7" type="radio" name="rating" value="7"  class="rating" >
                                          <label for="r7">7</label>
                                       </div>
                                       <div class="radio">
                                          <input id="r8" type="radio" name="rating" value="8"  class="rating" >
                                          <label for="r8">8</label>
                                       </div>
                                       <div class="radio">
                                          <input id="r9" type="radio" name="rating" value="9"  class="rating" >
                                          <label for="r9">9</label>
                                       </div>
                                       <div class="radio">
                                          <input id="r10" type="radio" name="rating" value="10"  class="rating" >
                                          <label for="r10">10</label>
                                       </div>
                                       <span>Extremely likely</span>
                                    </div>
                                    <p class="feedback-title">Tell us one thing we can do better</p>
                                    <textarea rows="1" style="min-height:35px" class="form-control suggesResize"  placeholder="Your answer" name="suggestion" onkeyup="getSuggeCount(this.value)" maxlength="500"></textarea>
                                    <span class=" text-left suggCount" style="text-align: left; color:#0d47a1; ">500</span>
                                    <p class="feedback-title">General comment</p>
                                    <textarea rows="1" style="min-height:35px" class="form-control genCommResize" placeholder="Your answer" name="comments" onkeyup="getCommCount(this.value)" maxlength="500"></textarea>
                                    <span class=" text-left commCount" style="text-align: left; color:#0d47a1; ">500</span>
                                 </div>
                                 <div class="modal-footer">
                                    <p class="blue-span">* required fields</p>
                                    <button class="btn btn-primary subButton" type="submit">Submit</button>
                                    <button type="button" id="thanku-feedbackpopup" class="btn btn-primary subButton" data-toggle="modal" data-target="#thanku-feedback" style="display:none;">
                                    </button>
                                 </div>
                              </form>
                           </div>
                        </div>
                     </div>
                     <!-- END FEEDBACK POPUP -->
                     <!-- Feedback Thankyou popup start-->
                     <div class="modal fade in" id="thanku-feedback" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" >
                        <div class="modal-dialog modal-sm" role="document">
                           <div class="modal-content">
                              <div class="modal-header">
                                 <button type="button" class="close-inner" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                 <h4 class="modal-title" id="myModalLabel">Thanks for your {{$data['current_quater']}} feedback.</h4>
                              </div>
                              <div class="modal-body">
                                 <p>You will be able to give more feedback in {{Carbon\Carbon::now()->addMonth(3)->format('F Y')}}.</p>
                              </div>
                              <div class="modal-footer">
                                 <button type="button" class="btn btn-primary right" data-dismiss="modal">OKAY</button>
                              </div>
                           </div>
                        </div>
                     </div>
                     <!-- Feedback Thankyou popup end-->
                  </div>
                  <div class="tab-inner-content feedback-column">
                     <div class="feedback-list {{ $data['feedback_current_status']['display_feedback']?'':'hidden' }}">
                        <form class="domain_management_form">
                           <div id="pdf_content" class="form_field_section">
                              <div class="input-group download-group text-center">
                                 <h1 class="text-center">Net Promoter Score {{@$nps}} <a class="helpicon" data-toggle="popover" data-trigger="hover" title=""  data-placement="right" data-content="NPS is an industry standard customer loyalty metric. It is calculated based on one question with a ranking from 1-10. Your overall score ranges from –100 to +100; a positive NPS is deemed to be good, whilst a NPS of +50 is excellent."><i class="fa fa-question-circle"></i></a></h1>
                                 <h3 class="text-center">{{$data['current_quater']}} Feedback for the  @if(isset($space_info_value))
                                    {{ $space_info_value->toArray()['seller_name']['company_name'] }} & {{ $space_info_value->toArray()['buyer_name']['company_name'] }}   @endif relationship
                                 </h3>
                                 <h4 class="text-center download_feedback_pdf" style="display:none">
                                    @if(!empty($feedback))
                                       @if($user_count > Config::get('constants.PDF_USERS_LIMIT'))
                                          <a href='javascript:void(0)' data-toggle="modal" data-target="#pdf_email_popup" >Download {{$data['current_quater']}} Feedback<span><img src="{{env('APP_URL')}}/images/ic_file_download.svg" alt="" ></span></a>
                                       @else
                                          <a href='{{ URL::to("downloadpdf/$clientsharid/$month/$currentDate")}}' target="_blank">Download {{$data['current_quater']}} Feedback<span><img src="{{env('APP_URL')}}/images/ic_file_download.svg" alt=""></span></a>
                                       @endif
                                    @else
                                    <a href='javascript:void(0)}}' style="cursor:not-allowed;" >Download {{$data['current_quater']}} Feedback<span><img src="{{env('APP_URL')}}/images/ic_file_download.svg" alt=""></span></a>
                                    @endif
                                 </h4>
                              </div>
                              <div class="input-group count-nps heading-text" >
                                 <p class="title">Number of respondees: @php if(!empty($feedback)){ echo sizeOfCustom($feedback); }else{ echo '0'; } @endphp </p>
                              </div>
                              @if(!empty($feedback))
                              @foreach($feedback as $feed)
                              @php 
                                 $feed->profile_image_url = getAwsSignedURL(filePathJsonToUrl($feed->profile_image));
                              @endphp
                              <div class="input-group feedback-user">
                                 <div class="feedback-user-wrap">
                                    @if(!empty($feed->profile_image_url))
                                    <div style="background-image: url('{{$feed->profile_image_url}}');" class="dp pro_pic_wrap"></div>
                                    @else 
                                    <div style="background-image: url('{{env('APP_URL')}}/images/dummy-avatar-img.svg');" class="dp pro_pic_wrap"></div>
                                    @endif 
                                    <div class="name-wrap">
                                       <a href="#!" class="title">{{ucfirst($feed->first_name)}} {{ucfirst($feed->last_name)}}</a>
                                       <span class="time">
                                       <a href="mailto:{{$feed->email}}" >
                                       {{$feed->email}}
                                       </a>
                                       </span>
                                    </div>
                                 </div>
                                 <div class="heading-text">
                                    <h1>Score: {{$feed->rating}}</h1>
                                    <h2>What {{ucfirst($feed->first_name)}} thinks @if(isset($space_info_value))
                                       {{ $space_info_value->toArray()['seller_name']['company_name'] }} @endif can do better
                                    </h2>
                                    <p>@if(!empty($feed->suggestion)){{$feed->suggestion}} @else <span class="no-comment"> No Comment </span> @endif</p>
                                 </div>
                                 <div class="heading-text">
                                    <h2>General comment</h2>
                                    <p>@if(!empty($feed->comments)){{$feed->comments}} @else <span class="no-comment"> No Comment </span> @endif</p>
                                 </div>
                              </div>
                              @endforeach
                              @endif
                              @if(!empty($get_non_feedback_user))
                                 @foreach($get_non_feedback_user as $non_feedback_user)
                                    @php
                                       $non_feedback_user['user']['profile_image_url'] = getAwsSignedURL(composeUrl($non_feedback_user['user']['profile_image']));
                                    @endphp
                                    
                                    @if(Session::get('space_info')['company_buyer_id'] == $non_feedback_user['user_company_id'])
                                    <div class="input-group feedback-user feedback-given">
                                       <div class="feedback-user-wrap">
                                          @if($non_feedback_user['user']['profile_image_url'])
                                          <div style="background-image: url('{{$non_feedback_user['user']['profile_image_url']}}');" class="dp pro_pic_wrap"></div>
                                          @else
                                          <div style="background-image: url('{{env('APP_URL')}}/images/dummy-avatar-img.svg');" class="dp pro_pic_wrap"></div>
                                          @endif  
                                          <div class="name-wrap">
                                             <a href="#!" class="title">{{ ucfirst($non_feedback_user['user']['first_name']) }} {{ ucfirst($non_feedback_user['user']['last_name']) }}</a>
                                             <span class="time">
                                             <a href="mailto:{{$non_feedback_user['user']['email'] }}" >
                                             {{ $non_feedback_user['user']['email'] }}
                                             </a>
                                             </span>
                                          </div>
                                          <div class="no-feedback-text">
                                             <h3>No feedback given</h3>
                                          </div>
                                       </div>
                                    </div>
                                    @endif
                                 @endforeach
                              @endif
                           </div>
                        </form>
                     </div>
                     <!-- feedback-list -->
                     <div class="give-feedback text-center {{ $data['feedback_current_status']['give_feedback']?'':'hidden' }}">
                        <h1>You haven't given feedback yet</h1>
                        <p>You have {{$data['feedback_current_status']['days_left']}} day(s) remaining to give feedback on the {{$clientsharname}} relationship for {{$data['current_quater']}}</p>
                        <button class="btn btn-primary" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#feedback-popup">Give feedback</button>
                     </div>
                     <!-- give-feedback -->
                     <div class="feedback-submitted text-center {{ $data['feedback_current_status']['display_feedback_tat']?'':'hidden' }}">
                        <h1>Thank you, your feedback has been submitted.</h1>
                        <p>You will see the overall feedback results from other members in {{ $data['feedback_current_status']['days_left']+1}} days.</p>
                     </div>
                     <!-- feedback-submitted -->

                     <div class="feedback-not-started text-center {{ $data['feedback_current_status']['seller_view']?'':'hidden' }}">
                        @if( Carbon\Carbon::now()->format('m y') == Carbon\Carbon::parse($data['feedback_current_status']['next_due'])->format('m y'))
                           <h1>{{$data['current_quater']}} Feedback is currently being collected.</h1>
                           <p>Feedback result will be available in {{ $data['feedback_current_status']['days_left'] }} days.</p>
                        @else
                           <h1>Feedback will be open soon.</h1>
                           <p>You will see the overall feedback results from other members on {{ $data['feedback_current_status']['days_left']+Carbon\Carbon::now()->day+1 }}{{Carbon\Carbon::parse($data['feedback_current_status']['next_due'])->format('-M-Y')}}.</p>
                        @endif
                     </div>
                     <!-- feedback-not-started -->
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane" id="user-management-tab">
                  <div class="heading_wrap">
                     <h4 class="title">User management</h4>
                     @if(!Session::get('space_info')['invite_permission'] || Session::get('space_info')['space_user'][0]['user_type_id'] == Config::get('constants.USER_ROLE_ID'))
                     <button class="invite-btn" type="button">INVITE COLLEAGUES</button>
                     @endif
                  </div>
                  <div class="tab-inner-content">
                     <div class="form_field_section">
                        <div class="tablerow tablehdrow">
                           <div class="col-lg-4 col-md-4 col-sm-5 col-xs-5 tablecell"><span class="approved-small-text">Members</span>
                           </div>
                           <div class="col-lg-5 col-md-5 col-sm-5 col-xs-4 tablecell"><span class="approved-small-text">Email address</span>
                           </div>
                           <div class="col-lg-3 col-md-3 col-sm-2 col-xs-3 tablecell"><span class="approved-small-text"></span>
                           </div>
                        </div>
                        <div class="tablerow">
                           <div class="col-lg-4 col-md-4 col-sm-5 col-xs-5 tablecell name_cell"><span class="approved-small-text">Members</span><span style="background-image: url('./images/avatar.svg');" class="dp pro_pic_wrap"></span><span class="mem_name">Chester James</span>
                           </div>
                           <div class="col-lg-5 col-md-5 col-sm-5 col-xs-4 tablecell"><span class="approved-small-text">Email address</span><span>chester@IBM.co.uk</span>
                           </div>
                           <div class="col-lg-3 col-md-3 col-sm-2 col-xs-3 tablecell"><a href="#" class="delete-link right" data-toggle="modal" data-target="#removeuserpopup">Remove user</a>
                           </div>
                        </div>
                        <div class="tablerow">
                           <div class="col-lg-4 col-md-4 col-sm-5 col-xs-5 tablecell name_cell"><span class="approved-small-text">Members</span><span style="background-image: url('./images/avatar.svg');" class="dp pro_pic_wrap"></span><span class="mem_name">Chester James</span>
                           </div>
                           <div class="col-lg-5 col-md-5 col-sm-5 col-xs-4 tablecell"><span class="approved-small-text">Email address</span><span>chester@IBM.co.uk</span>
                           </div>
                           <div class="col-lg-3 col-md-3 col-sm-2 col-xs-3 tablecell"><a href="#" class="delete-link right" data-toggle="modal" data-target="#removeuserpopup">Remove user</a>
                           </div>
                        </div>
                        <div class="tablerow">
                           <div class="col-lg-4 col-md-4 col-sm-5 col-xs-5 tablecell name_cell"><span class="approved-small-text">Members</span><span style="background-image: url('./images/avatar.svg');" class="dp pro_pic_wrap"></span><span class="mem_name">Chester James</span>
                           </div>
                           <div class="col-lg-5 col-md-5 col-sm-5 col-xs-4 tablecell"><span class="approved-small-text">Pending invitee</span><span>chester@IBM.co.uk</span>
                           </div>
                           <div class="col-lg-3 col-md-3 col-sm-2 col-xs-3 tablecell"><a href="#" class="delete-link right" data-toggle="modal" data-target="#removeuserpopup">Remove user</a>
                           </div>
                        </div>
                        @if(Session::get('space_info')['invite_permission'])
                        <div class="tablerow lastrow">
                           <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 tablecell"><a href="#">Invite colleagues</a>
                           </div>
                        </div>
                        @endif
                     </div>
                     <button class="btn btn-primary left disabled" href="">Save</button>
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane" id="pending-invites-tab">
                  <div class="heading_wrap">
                     <h4 class="title">Pending invites</h4>
                  </div>
                  <div class="tab-inner-content">
                     <div class="form_field_section">
                        <div class="tablerow tablehdrow">
                           <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 tablecell"><span class="approved-small-text">Pending invitee</span>
                           </div>
                           <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 tablecell"><span class="approved-small-text">Date invited</span>
                           </div>
                           <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 tablecell"><span class="approved-small-text">Invited by</span>
                           </div>
                           <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 tablecell"><span class="approved-small-text"></span>
                           </div>
                        </div>
                        <div class="tablerow">
                           <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 tablecell name_cell"><span class="approved-small-text">Pending invitee</span><span style="background-image: url('./images/avatar.svg');" class="dp pro_pic_wrap"></span><span class="mem_name">Chester James</span>
                           </div>
                           <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 tablecell"><span class="approved-small-text">Date invited</span><span>10/04/2017</span>
                           </div>
                           <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 tablecell"><span class="approved-small-text">Invited by</span><span>Wesley Cummings</span>
                           </div>
                           <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 tablecell">
                              <div class="dropdown">
                                 <a href="#" class="dropdown-toggle dots" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                 <span></span>
                                 </a>
                                 <ul class="dropdown-menu">
                                    <li><a href="#" data-toggle="modal" data-target="#resendinvites">Resend invite</a>
                                    </li>
                                    <li><a href="#" class="delete-link" data-toggle="modal" data-target="#cancelinvitepopup">Cancel invite</a>
                                    </li>
                                 </ul>
                              </div>
                           </div>
                        </div>
                        <div class="tablerow">
                           <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 tablecell name_cell"><span class="approved-small-text">Pending invitee</span><span style="background-image: url('./images/avatar.svg');" class="dp pro_pic_wrap"></span><span class="mem_name">Jenny Houston</span>
                           </div>
                           <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 tablecell"><span class="approved-small-text">Date invited</span><span>10/04/2017</span>
                           </div>
                           <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 tablecell"><span class="approved-small-text">Invited by</span><span>Franklin Shaw</span>
                           </div>
                           <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 tablecell">
                              <div class="dropdown">
                                 <a href="#" class="dropdown-toggle dots" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                 <span></span>
                                 </a>
                                 <ul class="dropdown-menu">
                                    <li><a href="#" data-toggle="modal" data-target="#resendinvites">Resend invite</a>
                                    </li>
                                    <li><a href="#" class="delete-link" data-toggle="modal" data-target="#cancelinvitepopup">Cancel invite</a>
                                    </li>
                                 </ul>
                              </div>
                           </div>
                        </div>
                        <div class="tablerow">
                           <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 tablecell name_cell"><span class="approved-small-text">Pending invitee</span><span style="background-image: url('./images/avatar.svg');" class="dp pro_pic_wrap"></span><span class="mem_name">Johanna Mathis</span>
                           </div>
                           <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 tablecell"><span class="approved-small-text">Date invited</span><span>10/04/2017</span>
                           </div>
                           <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 tablecell"><span class="approved-small-text">Invited by</span><span>Rosetta Rios</span>
                           </div>
                           <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 tablecell">
                              <div class="dropdown">
                                 <a href="#" class="dropdown-toggle dots" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                 <span></span>
                                 </a>
                                 <ul class="dropdown-menu">
                                    <li><a href="#" data-toggle="modal" data-target="#resendinvites">Resend invite</a>
                                    </li>
                                    <li><a href="#" class="delete-link" data-toggle="modal" data-target="#cancelinvitepopup">Cancel invite</a>
                                    </li>
                                 </ul>
                              </div>
                           </div>
                        </div>
                     </div>
                     <button class="btn btn-primary left disabled" href="">Save</button>
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane" id="notifications-tab">
                  <div class="heading_wrap">
                     <h4 class="title">Email Notifications</h4>
                  </div>
                  <div class="tab-inner-content">
                     <p>Send me an email when I am:</p>
                     <form>
                        <div class="form-group"><input id="post" type="checkbox" checked>
                           <label for="post">Alerted to a post</label>
                        </div>
                        <div class="form-group"><input id="comment" type="checkbox" >
                           <label for="comment">Alerted to a comment</label>
                        </div>
                     </form>
                     <button class="btn btn-primary left disabled" href="">Save</button>
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane" id="password-tab">
                  <div class="heading_wrap">
                     <h4 class="title">Password</h4>
                  </div>
                  <div class="tab-inner-content">
                     <p>Change your password here.</p>
                     <form class="change_password_form">
                        <div class="form_field_section">
                           <div class="col-lg-8 col-md-8 col-sm-10 col-xs-12 input-group">
                              <label>Current password</label>
                              <input type="text" class="form-control" placeholder="Type your password here" name="rule" value="">
                           </div>
                           <div class="col-lg-8 col-md-8 col-sm-10 col-xs-12 input-group">
                              <label>New password</label>
                              <input type="text" class="form-control" placeholder="Type your new password here" name="rule" value="">
                           </div>
                           <div class="col-lg-8 col-md-8 col-sm-10 col-xs-12 input-group">
                              <label>Verify password</label>
                              <input type="text" class="form-control no-margin" placeholder="Verify your password here" name="rule" value="">
                           </div>
                        </div>
                        <button class="btn btn-primary left disabled" href="">Save</button>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- col-md-8 -->
</div>
<!-- container -->
@if($user_count > Config::get('constants.PDF_USERS_LIMIT'))
<!---modal for email popup -->
<div class="modal fade" id="pdf_email_popup" role="dialog">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Download PDF<span class="add_share_name"></span></h4>
      </div>
      <div class="modal-body">
        <p>A link to download all Feedback data in PDF format has been emailed to {{Auth::user()->email}} and will be available shortly.</p>
      </div>
      <div class="modal-footer">
        <input type="hidden" class="access_token" value="{{ csrf_token() }}" />
        <input type="hidden" class="" value="">
        <input type="hidden" class="" value="">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!--end modal for email popup-->
@endif

<script type="text/javascript"> 
   var feedback_pdf_link = '<?php echo URL::to("downloadpdf/$clientsharid/$month/$currentDate"); ?>';
   @if (Session::has('successfeedback'))
    $('#thanku-feedbackpopup').trigger('click');
   @endif
</script>
<script rel="text/javascript" src="{{ url('js/custom/feedback_module.js?q='.env('CACHE_COUNTER', rand()),[],env('HTTPS_ENABLE', true)) }}"></script>
@endsection