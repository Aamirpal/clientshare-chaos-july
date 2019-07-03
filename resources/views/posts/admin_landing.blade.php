@extends(session()->get('layout'))
@section('content')
<input type="hidden" class="check_feedback_status" value="{{$give_feedback}}" >
<input type="hidden" class="check_feedback_on_off_status" value="{{$feedback_on_off_status}}" >
@php
  $exact_post_count = 1;
  $ssl = env('APP_ENV') == 'local'? false : true;
  $check_user_is_new = !isset($space_user[0]['metadata']['user_profile']);
  $is_logged_in_user_admin = isset($space_user) && $space_user[0]['user_role']['user_type_name'] == 'admin' ? 1 : 0;
  $check_buyer =  checkBuyerSeller($space_id,Auth::User()->id);

  $session_data = spaceSessionData($space_id);
@endphp
@if($check_user_is_new)
  @php
    $buyer_info = $data->BuyerName;
    $buyer_seller = [$data->BuyerName, $data->SellerName];
  @endphp
<script>
  $(document).ready(function(){
    $('#profile_information').modal('show');
  });
</script>
 <!-- Profile POPUP admin Start-->
 <meta name="csrf-token" content="{{ csrf_token() }}" />
 @php Session::put('spaceid',$space_id);
 Session::put('profile','new');
 $account_data =json_decode(Auth::User()->social_accounts);
@endphp
<div class="modal fade pro_info_member" id="profile_information" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="profile_information">
  <div class="modal-dialog " role="document">
    <div class="modal-content white-popup ">
      <div class="form-submit-loader register_loader form_loader" style="display:none"><span></span></div>
      <div class="modal-body">
        @if(!isset($account_data->linkedin))   
        <div class="linked_hide_overlay"></div>
        <div class="disable-overlay bio_blue_popup"></div>
        @endif
        <div class="form-submit-loader profile_loader" style="display:none"><span></span></div>
        <form method="post" action="{{ url('/update_admin_space_profile',[],$ssl) }}" enctype="multipart/form-data" onsubmit="$('.register_loader').show();">
          {{csrf_field()}}         
          <div class="image-section">
            <div class="profile-info-image">
            @php
            if(Auth::User()->profile_image_url !=''){
              $img_avatar = Auth::User()->profile_image_url;
            }elseif(isset($account_data->linkedin)){
              $img_avatar = $account_data->linkedin->user->pictureUrls->values[0]??url('/',[],$ssl)."/images/cam-pic.png";
            }else{
              $img_avatar =  url('/',[],$ssl)."/images/cam-pic.png";
            }
            @endphp
           
            <span style="background: url('{{$img_avatar}}') repeat scroll center center / cover; border-radius: 50%; display: inline-block; height: 100%;width: 100%;" alt="Profile_pic_empty"></span>
            <input type='file' name="file" onchange="readURL55(this);" id="img_show" style="display:none;" />
            <input name="linkedin_image" value="@if(isset($account_data->linkedin)){{@$account_data->linkedin->user->pictureUrls->values[0]}} @endif" type="hidden">
            <span class="uploaded_img show_image" id="blah55" style="border-radius: 50%; display: inline-block; height: 100%;width: 100%; background-position: center; background-size: cover;">
             <span class="fileinput-new" style="display:{{ Auth::User()->profile_image_url?'':'none' }}">
               <img src="{{url('/',[],$ssl)}}/images/ic_AddProfilePic.svg" alt="Camera" class="camera-icon1" />
             </span>
             <span class="fileinput-exists" id="show_change_image" style="display:none" >
               <img src="{{url('/',[],$ssl)}}/images/ic_AddProfilePic.svg" alt="Camera" class="camera-icon1" />
             </span>
           </div>
           <div class="profile_name_edit">
            <h3 class="modal-title profile-info text-center user_first_last_name" id="">{{ ucfirst(Auth::User()->first_name)??''}} {{ucfirst(Auth::User()->last_name)??''}} 
            <span class="edit_user_name edit-icon"><i class="fa fa-pencil"></i></span></h3>
            <input type="hidden" id="first_prev_name" value="{{ ucfirst(Auth::User()->first_name)??''}}">
            <input type="hidden" id="last_prev_name" value="{{ucfirst(Auth::User()->last_name)??''}}">
            <div class="edit_user" style="display:none">
              <div class="first_name_field">
                <input type="text" class="form-control jobtitle_admin c_side_validation" id="first_name" placeholder="First Name" name="first_name" value="@if(Auth::User()->first_name){{ Auth::User()->first_name??''}}@endif" autocomplete="off">
              </div>
              <div class="last_name_field">
                <input type="text" class="form-control jobtitle_admin c_side_validation" id="last_name" placeholder="Last Name" name="last_name" value="@if(Auth::User()->last_name){{Auth::User()->last_name??''}}@endif" autocomplete="off">
              </div>
              <span class="cancel_user_name left"><i class="fa fa-times"></i></span>
            </div>
          </div>
        </div>
        <div class="form-section ">
          <h3 class="modal-title profile-info register-title" id="myModalLabel">Profile information</h3>
          <label>Changes only apply to this Client Share.</label>

          <div class="linkedinbtn biotextarea" style="display: none">
           <a href="#" class="btn btn-default tourlink_yes_linkedin"><i class="fa fa-linkedin"></i> COMPLETE PROFILE WITH LINKEDIN</a>
           <div class="profile_linkedin_wrap">
             <div class="popover  tour profile_linkedin" id='profile_linkedin' style="display: none">
               <div class="arrow_box_wrap"><span class="arrow_box"></span></div><div class="popover-content">Want to save time? Complete your profile with LinkedIn</div><div class="popover-navigation"><button class="tourlink tourlink_yes_linkedin" type="button">Yes</button><button class="tourlink tourlink_no_linkedin" type="button">No</button></div>
             </div>
           </div>
         </div>

         <div class="form-group">
           <label>Your job title <span class="required-star">&nbsp; *</span></label>
           <textarea id="jobtitletxt" class="form-control jobtitle_admin txtarea linked_cls c_side_validation" placeholder="e.g. Procurement manager" name="jobtitle" autocomplete="off">{{ old('jobtitle')??(Session('linkedin_job_title')??$previous_space_data['metadata']['user_profile']['job_title']?? ($account_data->linkedin->user->positions->values[0]->title ?? '' ) ) }}</textarea>
           <div class="admin_job_error">
            @if ($errors->has('jobtitle'))
            <span class="error-msg text-left">
              {{ $errors->first('jobtitle') }}
            </span>
            @endif
          </div>
        </div>
        <input type="hidden" value="{{$buyer_info['company_name']}}" buyer-id="{{$buyer_info['id']}}" sub-comp-active="{{Session::get('space_info')['sub_companies']}}" class="buyer_info_hidden" autocomplete="off">
        <div class="form-group">
          <label  class="comp_lab">@if(Session::get('space_info')['sub_companies'] == '1') Community @else Company @endif <span class="required-star">&nbsp; *</span></label>
 
          @if(Session::get('space_company'))
          <input type="text" id="company_name_check" name="company_name" class="form-control" readonly="readonly" placeholder="Job title" value="@if(Session::get('space_company')!=''){{Session::get('space_company')['company_name']??''}}@endif">
          <input type="hidden" name="company" class="form-control"  placeholder="Job title" value="@if(Session::get('space_company')!=''){{Session::get('space_company')['id']??''}}@endif">
          @else
          <select name="company" id="landing_company" class="selectpicker company_admin form-control c_side_validation" title="select">
            @foreach($buyer_seller as $buyer_seller_name)
            <option value="{{$buyer_seller_name['id']}}"  @if (old('company') == $buyer_seller_name['id']) selected="selected" @endif>{{$buyer_seller_name['company_name']}}</option>
            @endforeach
          </select>
          @endif
          <div class="admin_company_error">
            @if ($errors->has('company'))
            <span class="error-msg text-left">
              {{ $errors->first('company') }}
            </span>
            @endif
          </div>
        </div>
        @if(Session::get('space_info')['sub_companies'] == '1')
        <div class="form-group sub_comp_div" style="display:none">
         <label>Company <span class="required-star">&nbsp; *</span></label>
         <input type="text" class="form-control sub_comp_input" placeholder="Start typing to add your company" value="{{old('sub_comp') }}" autocomplete="off">

         <div id="suggesstion-box"></div>
         <div class="admin_company_error">
          @if ($errors->has('sub_comp'))
          <span class="error-msg text-left">
            {{ $errors->first('sub_comp') }}
          </span>
          @endif
        </div>
      </div>
      @endif
      <div class="form-group biotextarea" >
        <div class="pf_bio" style="display: none;">
          <div class="popover tour bio_blue_popup bio_blue_popup_linkedin pf_bio" style="display: none !important">
            <div class="arrow_box_wrap"><span class="arrow_box"></span></div><h3 class="popover-title">Would you like to edit your Bio?</h3><div class="popover-content">Your Bio is specific to each Client Share so can be tailored to each relationship.</div><div class="popover-navigation"><button class="tourlink tourlink_yes">Yes</button><button class="tourlink tourlink_no">No</button></div></div>
          </div>
          <label>Bio</label>

          <textarea id="biotextarea" placeholder="How would you describe your responsibilities?" class="form-control linked_cls" name="bio" maxlength="300"
          onkeyup="countCharBio(this)" style="max-height:100px !important;;overflow-y:hidden!important">{{old('bio')??(
          $previous_space_data['metadata']['user_profile']['bio']?? ($account_data->linkedin->user->headline ?? '') ) }}</textarea>
          <span class="letter-count">
            <div id="charNumbio" class="left" val="{{$len??0}}"></div>
            /300
          </span>
        </div>
        <h3 class="modal-title contact-info register-title" id="myModalLabel">Contact information</h3>
        <label>Changes apply to all of your Client Share’s.</label>
        <div class="form-group">
         <label>LinkedIn Profile</label>
 
         @php
         $val_linkedin='';
         if(!empty(Auth::User()['contact']['linkedin_url'])){
          $val_linkedin = Auth::User()['contact']['linkedin_url'];
        }
        if(old('linkedin')){
          $val_linkedin = old('linkedin');
        }
        @endphp
        <input type="text" class="form-control linked_cls" placeholder="Paste your LinkedIn profile here" id="linkedin_link" name="linkedin" value="{{$val_linkedin}}">
      </div>

      <div class="form-group">
       <label>Email address</label>
       <input type="text" class="form-control" readonly="readonly" placeholder="e.g. Procurement manager" value="{{Auth::User()->email}}">
     </div>
     <div class="form-group">
       <label>Phone number</label>
   
      <input type="text" class="form-control ph_number" placeholder="Enter your phone number here" name="phone" value="{{ old('phone')??(Auth::User()['contact']['contact_number']??'') }}" onfocus="this.value = this.value;" autocomplete="off">

      <div class="admin_company_error">
        @if ($errors->has('phone'))
        <span class="error-msg text-left">
          {{ $errors->first('phone') }}
        </span>
        @endif
      </div>
    </div>
    <div class="form-group">
     <div class="required-fields">*Required fields</div>
     <input type="hidden" name="space_id" class="hidden_sp_id"  value="{{$space_id}}">
     <button class="btn btn-primary right save_admin_profile">SAVE PROFILE</button>
   </div>
 </div>
</form>
</div>
<div class="modal-footer">
</div>
<div id="load_more" class="load_more load-popup-register" style="float: left; width: 100%; text-align: center; margin-top: 20px; display:none;">
  <img src="{{env('APP_URL')}}/images/puff.svg" class="show_puff">
</div>
</div>
<!-- white popoup -->
</div>
</div>
@elseif($is_logged_in_user_admin == 1 && $session_data['share_setup_steps'] <= config('constants.MAX_SHARE_SETUP_STEPS'))
<script>
  $(document).ready(function () {
    runOnBoardingTour();
  });
</script>
@elseif(Auth::user()->show_tour==true && $is_logged_in_user_admin == 0)
<script>
  $(document).ready(function () {
    runTour();
  });
</script>
@endif

@if ($errors->has('summary_file_error'))
<span class="error-msg text-left">
  {{ $errors->first('summary_file_error') }}
</span>
@endif

<textarea 
    contentEditable="true"
    readonly="false"
    class="ios_copy_link_hidden_textarea"
    id="copy_post_link_ios"></textarea>

<meta name="csrf-token" content="{{ csrf_token() }}" />
<section class="content-section">
<div class="container">
<div class="container-prime" style="display:none"></div>
<div class="row">
  
<div class="col-xs-12 text-center" style="display:none">
    <div class="finish-setup-alert full-width">
      <p>You have <span>4</span> more tasks to complete the page profile. <a href="#">Click here to finish setup</a></p>
    </div>
</div>
 
 <div class="col-xs-12 hidden-sm hidden-md hidden-lg user-profile-status-mobile user-profile-status-show-mobile user-profile-status-col">
  </div>

<div class="col-xs-12 col-lg-5 pull-left feed-col left-content" id="left-content">
 <div class="feed-left-part full-width theiaStickySidebar">
 <div class="user-profile-status-show user-profile-status-col">
</div>

 @include('posts/admin_landing_leftbar')
 </div>
 @include('layouts.quick_links')
 @include('layouts.invite_colleague')
 @include('posts/twitter_popup')
</div>
@include('posts.user_profile_progress')
<div class="col-xs-12 col-lg-7 pull-right feed-col-right mid-content">
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <div class="add_post_form_ajax">
      
  </div>
  @include('posts/admin_landing_category')

  @for($i=0; $i<2; $i++)
    <div class="lazy-loading post">
     <div class="user-img">
        <p class="pic"></p>
        <p class="name"></p>
        <p class="pull-right one"></p>
        <p class="pull-right two"></p>
        <br>
        <p class="name2"></p>
        <p class="name3"></p>
      </div>
      <div class="content">
        <p class="one"></p><br>
        <p class="two"></p><br>
        <p class="one"></p><br>
        <p class="three"></p>
      </div>
    </div><!-- lazy-loading -->
  @endfor
@include('posts/admin_landing_post')
<input type="hidden" value="{{$exact_post_count}}" class="visibility_alert_count">
<input type="hidden" value="{{$case_post}}" class="load_more_type">
<input type="hidden" name="space_id_hidden" class="space_id_hidden" value="{{$space_id}}">
<input type="hidden" name="post_show_hidden" class="post_show_hidden" value="{{$post_show}}">
<input type="hidden" name="load_ajax_new_posts" class="load_ajax_new_posts" value="0">
<div class="modal fade endrose add_scroll visibility-setting-modal" id="visibility_setting_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
</div>
<div id="load_more_post" class="load_more_post" style="float: left; width: 100%; text-align: center; margin-top: 20px; display:none;">
  <img src="{{env('APP_URL')}}/images/puff.svg" class="show_puff">
</div>
<div id="ajax_div_pagination"></div>
</div>
<!-- POST end -->
</div>
</div>
</section>

</div>
<div class="edit_popup_skull" style="display:none"></div>
</div>
</section>

@if( $check_buyer == 'buyer' && $feedback_status['feedback_status'] &&
$feedback_status['current_month_eligible'] && !sizeOfCustom($feedback_status['user_current_quater_feedback']))
    <a href="javascript:void();" data-toggle="modal" data-target="#feedback-popup" class="give-feedback-buyer"></a>
@endif
<!-- Modal -->
@include('posts/modal')
@include('posts/onboarding_popup', ['onboarding_data'=>objectToArray(Session::get('space_info')), 'user' => objectToArray(Auth::user())])
<div class="modal fade endrose endorse_popup_modal add_scroll" id="endoresedpopup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"></div>
<div class="modal fade" id="discardModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
 <div class="modal-dialog modal-sm" role="document">
  <div class="modal-content">
   <div class="modal-header">
    <button type="button" class="close-inner" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">Discard post</h4>
  </div>
  <div class="modal-body">
    <p>Are you sure you want to discard this post, the post content will be lost?</p>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default left" data-dismiss="modal">cancel</button>
    <a href="<?php echo $_SERVER['REQUEST_URI'];?>" id="discard" class="btn btn-primary modal_initiate_btn" >Discard Post</a>
  </div>
</div>
</div>
</div>
<!-- Endrose Modal -->
<!-- User info Model on Visible start -->
<div class="modal fade community-mem-detail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
</div>
<div class="modal fade add_scroll eye_users_popup" id="visiblepopup1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" ></div>

@php
  $ssl = env('SSL', true);
@endphp

<link rel="stylesheet" type="text/css" href="{{ url('/css/bootstrap-suggest.css?q='.env('CACHE_COUNTER', rand()),[],$ssl) }}">


<link rel="stylesheet" type="text/css" href="{{ url('/css/jquery-ui.css?q='.env('CACHE_COUNTER', rand()),[],$ssl) }}">

<script>
$('.mail_body').autosize();
var single_post_view = {{$single_post_view}};
var single_post_id = '{{$single_post_id}}';
feedback_flag = false;
@if(isset($_REQUEST['feedback']) && $_REQUEST['feedback'] == 'true')
  feedback_flag = true;
@endif
</script>

<script rel="text/javascript" src="{{ mix('js/compiled/feed_page.js') }}"></script>


<script type="text/javascript">
  $(document).ready(function(){
    validateProfileForm1();
    $("#comment_input_area").attr("disabled", false);
    $("#comment_input_area").css("cursor", "default");
    $('.load-popup-register').remove();

    $('.tourlink_yes').on('click', function(){
      $('.bio_blue_popup').remove();
      $('#biotextarea').focus();
      $(".linked_hide_overlay").hide();
      return false;
    });
    $('.tourlink_no').on('click', function(){
      $('.bio_blue_popup').remove();
      $('button[data-id=landing_company]').click();
      $(".linked_hide_overlay").hide();
      return false;
    });
  });
 
  $('.company_admin').on('change',function(){

    var nval= $(this).find('option:selected').val();
    if(nval != ""){
      $('.admin_company_error').hide();
    }
  });

      function countCharBio(val) {
        var len = val.value.length;
        var bio_count = $('#charNumbio').val();
        $('#charNumbio').text(bio_count + len);
      };
      
    </script>
    <!-- Modals dump area -->
    <div class="modals_dump_area"></div>
    <!-- Modals dump area -->

    <!--DELTE COMMENT POPUPU START-->
    <div class="modal fade delete-modal-comment" id="delete_modal_comment" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
     <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
       <div class="modal-header">
        <button type="button" class="close-inner" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Delete comment</h4>
      </div>
      <div class="modal-body">
        <p>This will permanently delete the comment from the Post.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default left" data-dismiss="modal">CANCEL</button>
        <a href="javascript:void(0)" class="btn btn-primary modal_initiate_btn del_comment" id="" commentid="" spaceid="" onclick="delete_comment_confirm(this)">DELETE</a>
      </div>
    </div>
  </div>
</div>
<!--DELTE COMMENT POPUPU END-->
<!--DISCARD EDIT COMMENT POPUPU START-->
<div class="modal fade" id="discardModalcomment" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
 <div class="modal-dialog modal-sm" role="document">
  <div class="modal-content">
   <div class="modal-header">
    <button type="button" class="close-inner" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">Discard comment</h4>
  </div>
  <div class="modal-body">
    <p>Are you sure you want to discard comment you edit.</p>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default left" data-dismiss="modal">CANCEL</button>
    <a href="javascript:void(0)" class="btn btn-primary modal_initiate_btn discard_comment" id="" commentid="" spaceid="" onclick="discardComment()">DISCARD</a>
  </div>
</div>
</div>
</div>
<!--DISCARD EDIT COMMENT POPUPU END-->
<div class="modal fade community-member-detail" id="member_info_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
   <div class="modal-content">
    <div class="modal-header">
     <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{env('APP_URL')}}/images/ic_highlight_removegray.svg" alt="" /></button>
   </div>
   <div class="modal-body">
     <div class="modal_image_section">
     </div>
     <div class="modal_content_section community_member_info">
      <div class="member_info">
       <h4></h4>
       <h5></h5>
       <h6>JOBTITLE</h6>
       <p></p>
       <div class="contact-info">
        <h6>Contact information</h6>
        <span class="email-link"></span>
        <span class="linkedin-link"></span>
        <span class="call-link"></span>
      </div>
    </div>
  </div>
</div>
</div>
</div>
</div>


<script>
  /* log home page visit */
  window.onload = function(){
  // $(document).trigger('scroll');
  custom_logger({
    'description' : 'visit homepage',
    'action' :'visit homepage'
  });
}

function fillSessionData(){
    @if(Session("linkedin_job_title")!='')
        $('#jobtitletxt').val('{{Session("linkedin_job_title")}}');
    @endif
    @if(Session("linkedin_phoneno")!='')
        $('.ph_number').val('{{Session("linkedin_phoneno")}}');
    @endif
     @if(Session("linkedin_bio")!='')
        $('#biotextarea').val('{{Session("linkedin_bio")}}');
     @endif
    @if(Session("linkedin_link")!='')
        $('#linkedin_link').val('{{Session("linkedin_link")}}');
    @endif
    @if(Session("linkedin_company")!='')
      $('#landing_company').val('{{Session("linkedin_company")}}');
      $('#landing_company').selectpicker('refresh');
    @endif
    @if(Session("linkedin_sub_company")!='')
        $('.sub_comp_input').val('{{Session("linkedin_sub_company")}}');
        $('.sub_comp_div').show();
    @endif
    @if(Session("linkedin_company_status")==1)
      $('.sub_comp_div').show();
    @endif
}

function linkedin_popup(){
    fillLinkedinData();
    fillSessionData(); 
    $(".profile_linkedin_wrap").hide();
    $(".linked_hide_overlay").hide();
    $('.linkedinbtn').hide();
    $('.pf_bio').show();
    $('#jobtitletxt').focus();
    return false;
}

$(document).ready(function(){
  <?php if(Session::has('buyer')){ ?>
    $('#temp_id_trigger').trigger('click');
    linkedin_popup();
    <?php } ?>
    
    var linkedin_data = '<?php echo $_GET["linkedin"]??'';?>';
    if(linkedin_data == 'yes' ){
     linkedin_popup();
    }
  });

</script>
<script>
  function validateProfileForm1() {
    var isValid = true;
    $('.linked_cls').each(function() {
      if ( $(this).val() === '' ){
        isValid = false;
      }   
    });
    if(isValid === false){
      $('.linkedinbtn').show();
      $('.pf_bio').hide();
      $(".profile_linkedin_wrap").show();
      $(".linked_hide_overlay").show();
    }else{
      if($('#biotextarea').val() !=''){
        $('.pf_bio').show();
      }
    }
  }

  /* Migrate executive summary popups */
  $(document).ready(function(){
    $('.executive-summary-preview').find('.modal').each(function(){
      clone = $(this).clone();
      $('.modals_dump_area').append(clone);
      $(this).remove();
    });
  });
  /* Migrate executive summary popups */
</script>
@include('generic.file_viewer')
<!-- single-post-view popoup begins -->
<div class="modal fade custom-tile-popup single-post-popup in" id="single_post_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
   <div class="modal-dialog" role="document">
      <div class="modal-content white-popup">
         <div class="modal-body">
              <span class="close_ajax_modal" data-dismiss="modal" aria-label="Close">
                <img src="{{url('/',[],$ssl)}}/images/ic_delete_hover.svg" />
              </span>
             <p class="single_post_content">
                 
             </p>
         </div>
      </div>
   </div>
</div>
<!-- single-post-view popoup ends -->
<div class="post-modal">
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
               <p class="feedback-title">How likely are you to recommend {{Session::get('space_info')['SellerName']['company_name']}} to a friend or colleague?<span style="color: #0d47a1;"> *</span><a class="helpicon" data-toggle="popover" data-trigger="hover" title=""  data-placement="right" data-content="NPS is an industry standard customer loyalty metric. It is calculated based on one question with a ranking from 1-10. Your overall score ranges from –100 to +100; a positive NPS is deemed to be good, whilst a NPS of +50 is excellent."><i class="fa fa-question-circle"></i></a></p>
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
</div>
<span data-toggle="modal" data-target="#welcome_tour" class="tour-trigger"></span>
@include('posts/comment/comment_attachment_preview')
@endsection