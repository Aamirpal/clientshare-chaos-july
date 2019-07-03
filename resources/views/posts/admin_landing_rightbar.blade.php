@php $space_info_right_pannel = Session::get('space_info')->toArray(); @endphp
<div class="col-md-3 right-content hidden-sm hidden-xs {{ isset($single_post_view)?'single-post-view':'' }}" id = "right-content">
   <div class="lazy-loading top-post" style="display:{{isset($single_post_view)?'none':'block'}}">
    <div class="user-img">
      <p class=" two"></p>
      <br>
      <div class="inner-box">
        <p class="name"></p><br>
        <p class="name2 left"></p>
        <p class="name3"></p>
        <p class="name2"></p>
      </div>
      <p class="name"></p>
    </div>
  </div>
  <div class="box top-post-front hidden" style="display:{{isset($single_post_view)?'none':'block'}}">
    <h4 class="title">Top Posts</h4>
    <div class="top-post-nav">
      <span class="last-month"><img src="{{env('APP_URL')}}/images/ic_chevron_left0.svg" alt=""></span>
      <span class="curnt-month" id="curnt-month" yearnum="<?php echo date('Y');?>" monnum="<?php echo date('n');?>"><?php echo date('M');?></span>
      <span class="next-month" id="next-month" curr_month="<?php echo date('n');?>" curr_year="<?php echo date('Y');?>"><img src="{{env('APP_URL')}}/images/ic_chevron_right0.svg" alt=""></span>
      <ul>
        <li class="t_post active top_post_all">All</li>
        <li class="t_post top_post_seller">@if(isset($space_info_right_pannel['seller_name']['company_name'])){{$space_info_right_pannel['seller_name']['company_name']}} @endif</li>
        <li class="t_post top_post_buyer">@if(isset($space_info_right_pannel['buyer_name']['company_name'])){{$space_info_right_pannel['buyer_name']['company_name']}} @endif</li>
      </ul>
    </div>
    <div class="top-post-ajax-div">
    @if( !sizeOfCustom($toppost) )
     @include('layouts.sidebar_top_post_box')
     @endif
     @php $sr_no=1; @endphp

     @foreach($toppost as $tpostdata)
     
     @if(!isset($req_data['view_share']))
     <a href="{{url('/clientshare',[],env('HTTPS_ENABLE', true))}}/{{$tpostdata['post_details'][0]['space_id']}}/{{$tpostdata['post_details'][0]['id']}}" class="top-list">
      @endif
          <div class="box">
              <h4 class="title">{{$tpostdata['post_details'][0]['user']['first_name']}} {{$tpostdata['post_details'][0]['user']['last_name']}}</h4>
             
              
              @if( strlen($tpostdata['post_details'][0]['post_subject']) > 55)
              <p class="invite-btn">{{substr($tpostdata['post_details'][0]['post_subject'], 0, 55).'...'}}</p>
              @else
              <p class="invite-btn">{{$tpostdata['post_details'][0]['post_subject']}}</p></a>
              <span class="time"><?= date('F d, H:i ',strtotime($tpostdata['post_details'][0]['created_at']))?> </span>
              @endif
          </div>
    @if(!isset($req_data['view_share']))
      </a> 
    @else
     </div>
     @endif
     @php $sr_no++ @endphp
     
     @endforeach
     </div>
    </div>
  <div class="box members" id="tour3">
   <h4 class="title">Your Community</h4>
   @if( !sizeOfCustom($community_user) )
   <span class="light-span">There are no members</span>
   @endif
   <div class="member-wrap">
      <div class="pending-invitation-wrap">
         @foreach( $community_user as $key)
         <div class="pending-invitation">
            <a href="#!" data-toggle="modal" data-target="#myCommuModal{{$key['user_id']}}" class="title" data-id="{{$key['user']['id']}}" onclick="liked_info(this);">
               <div style="background-image: url('{{ $key['user']['profile_image_url']??env('APP_URL').'/images/dummy-avatar-img.svg' }}');" class="dp pro_pic_wrap" ></div>
               <div class="name-wrap">
                  {{ ucfirst($key['user']['first_name']) }} {{ ucfirst($key['user']['last_name']) }}
                  <span class="time">@if(isset($key['metadata']['user_profile']['job_title']))
            {{ $key['metadata']['user_profile']['job_title'] }}</span>
                </div>
            </a>
          </div>
            @endif

         @endforeach
            </div>
         
      </div>
      <a href="{{env('APP_URL')}}/community_members/{{$data->id}}" class="blue-links">See all your community</a>
      @if(!Session::get('space_info')['invite_permission'] || Session::get('space_info')['space_user'][0]['user_type_id'] == Config::get('constants.USER_ROLE_ID'))
      <div class="btn-wrap">
         <button type="button" class="invite-btn" data-toggle="modal" data-target="#myModalInvite">
         Invite Colleagues
         </button>
         <a href="{{env('APP_URL')}}/setting/{{$data->id}}#pending-invites-tab" class="blue-links pending-invites-link">See pending invites</a>
      </div>
      @endif 
   </div>

@if( $checkBuyer == 'buyer' && $feedback_status['feedback_status'])
  <!--  give feedback start  -->
  @if($feedback_status['current_month_eligible'] && !sizeOfCustom($feedback_status['user_current_quater_feedback']))
    <div class="box feedback">
      <div class="feedback-inner-wrap">
        <h4 class="title">Feedback</h4>
        <span> You have {{$data['days_left']}} day(s) remaining to give feedback for {{$data['quater']}}.</span>
      </div>
      <div class="btn-wrap">
        <button type="button" class="invite-btn feedbackbtn" data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#feedback-popup">GIVE FEEDBACK</button>
      </div>
    </div>
  <!--  give feedback end  -->

  <!-- Feedback givin start  -->
  @elseif( $feedback_status['current_month_eligible_v2'] && sizeOfCustom($feedback_status['user_current_quater_feedback']))
  <div class="box feedback">
    <div class="feedback-inner-wrap">
      <h4 class="title">Feedback</h4>
      <p class="again-feedback-text">Thank you for submitting feedback, results will be available in {{$data['days_left']+1}} day(s).</p>
    </div>
  </div>
  <!-- Feedback givin end  -->
  @else
  <!-- see all feedback list  -->
    <div class="box feedback">
    <div class="feedback-inner-wrap">
      <h4 class="title">Feedback</h4>
      @php
        $curr_month = date('n');
        $curr_year =  date('Y');
      @endphp
      <a href="{{env('APP_URL')}}/feedback/{{$curr_month}}/{{$curr_year}}/{{ Session::get('space_info')['id'] }}" class="blue-span">See all feedback results</a>
    </div>
  </div>
  <!-- see all feedback list  -->
  @endif
@endif
</div>

@include('layouts.invite_colleague')

<!--  FEEDBACK POPUP -->
<div id="feedback-popup" class="modal fade feedback-popup" data-backdrop="static" data-keyboard="false" role="dialog">
   <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <div class="modal-header">
         <h4 class="modal-title">{{ $data['quater'] }}</h4>
      </div>
      <div class="modal-content">
         <form method="post" action="{{ url('/saveFeedback',[],env('HTTPS_ENABLE', true)) }}" enctype="multipart/form-data" class="feedback_form">
            {!! csrf_field() !!}
            <input type="hidden" name="space_id" value="{{ Session::get('space_info')['id'] }}">
            <input type="hidden" name="user_id" value="{{Auth::user()->id}}">
            <input type="hidden" name="home" value="home">
            <div class="modal-body">
               <h1>Your opinion is important.</h1>
               <h2>We'd like to hear it.</h2>
               <p class="feedback-title">How likely are you to recommend {{$space_info_right_pannel['seller_name']['company_name']}} to a friend or colleague?<span style="color: #0d47a1;"> *</span><a class="helpicon" data-toggle="popover" data-trigger="hover" title=""  data-placement="right" data-content="NPS is an industry standard customer loyalty metric. It is calculated based on one question with a ranking from 1-10. Your overall score ranges from –100 to +100; a positive NPS is deemed to be good, whilst a NPS of +50 is excellent."><i class="fa fa-question-circle"></i></a></p>
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
               <span class=" text-left suggCount" style="text-align: left;color:#0d47a1; ">500</span>
               <p class="feedback-title">General comment</p>
               <textarea rows="1" class="form-control genCommResize" placeholder="Your answer" name="comments" style="min-height:35px" onkeyup="getCommCount(this.value)" maxlength="500"></textarea>
               <span class=" text-left commCount" style="text-align: left;color:#0d47a1; ">500</span>
            </div>
            <div class="modal-footer">
               <p class="blue-span">* required fields</p>
               <button class="btn btn-primary subButton" type="submit">Submit</button>
               <button type="button" id="thanku-feedbackpopup" class="btn btn-primary subButton" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#thanku-feedback" style="display:none;">
               </button>
            </div>
         </form>
      </div>
   </div>
</div>
<!-- END FEEDBACK POPUP -->
@php $month = date('F'); @endphp
<!-- Feedback Thankyou popup start-->
<div class="modal fade in" id="thanku-feedback" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" >
   <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close-inner" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            <h4 class="modal-title" id="myModalLabel">Thanks for your {{$data['quater']}} feedback.</h4>
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
<div id="hidden_id" hidden-id="{{$space_info_right_pannel['space_user'][0]['space_id']}}"></div>
<!-- Feedback Thankyou popup end-->

<script type="text/javascript">
  @if (Session::has('successfeedback'))
    $('#thanku-feedbackpopup').trigger('click');
  @endif
</script>
    