<div class="modal fade pro_info_member" id="user_profile" data-backdrop="static" data-keyboard="false"  tabindex="-1" role="dialog" aria-labelledby="user_profile">
 <div class="modal-dialog " role="document">
  <div class="modal-header">
   <span class="success-msg white_box_info" style="display:none">Restricted email access to domains </span>
   <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{ env('APP_URL') }}/images/ic_highlight_removegray.svg" alt=""></button>
  </div>
     
  <div class="modal-content white-popup ">
  <div class="modal-body">
    <div class="form-submit-loader profile_loader" style="display:none"><span></span></div>
      <form method="post" action="{{ url('/update_share_user',[],env('HTTPS_ENABLE', true)) }} " enctype="multipart/form-data" class="profile_update_form" onsubmit="$('.profile_loader').show();">
        <input type="hidden" name="space_id" value="{{Session::get('space_info')['id']}}">
        {{csrf_field()}}
        <div class="image-section">
          <div class="profile-info-image">
           @php
           $profile_image = Auth::user()->profile_image_url??url('/').'/images/cam-pic.png';
           if(!isset(Auth::user()->profile_image_url) && (session('linked')|| session('buyer'))){
           $profile_image = $account_data->linkedin->user->pictureUrls->values[0]??'';
         }
         $profile_image = strlen($profile_image)?$profile_image:url('/').'/images/cam-pic.png';
         @endphp
         <span style="background:  url('{{$profile_image}}') repeat scroll center center / cover; border-radius: 50%; display: inline-block; height: 100%;width: 100%;" alt="Profile_pic_empty"></span>
         <input type='file' name="file" onchange="readURL55(this);" id="img_show" style="display:none;" />
         <input name="linkedin_image" value="@if(isset($account_data->linkedin)){{@$account_data->linkedin->user->pictureUrls->values[0]}} @endif" type="hidden">
         <span class="uploaded_img show_image" id="blah55"  style=" border-radius: 50%; display: inline-block; height: 100%;width: 100%; background-position: center; background-size: cover;">
           <span class="fileinput-new" style="display:{{ Auth::user()->profile_image_url?'':'none'}}">
            <img src="{{env('APP_URL')}}/images/ic_AddProfilePic.svg" alt="Camera" class="camera-icon1" />
          </span>
        </div>
        <div class="profile_name_edit">
          <h3 class="modal-title profile-info text-center user_first_last_name" id="">{{ ucfirst(Auth::User()->first_name)??''}} {{ucfirst(Auth::User()->last_name)??''}} 
          <span class="edit_user_name edit-icon"><i class="fa fa-pencil"></i></span></h3>
          <input type="hidden" id="first_prev_name" value="{{ ucfirst(Auth::User()->first_name)??''}}">
          <input type="hidden" id="last_prev_name" value="{{ucfirst(Auth::User()->last_name)??''}}">
          <div class="edit_user" style="display:none">
            <div class="first_name_field">
              <input type="text" class="form-control jobtitle_admin" id="first_name" placeholder="First Name" name="user[first_name]" value="@if(Auth::User()->first_name){{ Auth::User()->first_name??''}}@endif" autocomplete="off">
              <span class="first_name_text_error" style="display:none"></span>
              @if ($errors->has('user.first_name'))
              <span class="error-msg text-left">
              {{ $errors->first('user.first_name') }}
              </span>
              @endif
            </div>
            <div class="last_name_field">
              <input type="text" class="form-control jobtitle_admin" id="last_name" placeholder="Last Name" name="user[last_name]" value="@if(Auth::User()->last_name){{Auth::User()->last_name??''}}@endif" autocomplete="off">
              <span class="last_name_text_error" style="display:none"></span>
              @if ($errors->has('user.last_name'))
              <span class="error-msg text-left">
                {{ $errors->first('user.last_name') }}
              </span>
              @endif
            </div>
            <span class="cancel_user_name left"><i class="fa fa-times"></i></span>
          </div>
        </div>
      </div>
      <div class="form-section">
        <h3 class="modal-title profile-info" id="myModalLabel">Profile information</h3>
        <div class="linkedinbtn biotextarea" style="display: none">
          <a href="#" class="btn btn-default tourlink_yes_linkedin hide"><i class="fa fa-linkedin"></i> COMPLETE PROFILE WITH LINKEDIN</a>
          <div class="profile_linkedin_wrap hide">
            <div class="popover  tour profile_linkedin" id='profile_linkedin' style="display: none">
              <div class="arrow_box_wrap"><span class="arrow_box"></span></div>
              <div class="popover-content">Want to save time? Complete your profile with LinkedIn</div>
              <div class="popover-navigation"><button class="tourlink tourlink_yes_linkedin" type="button">Yes</button><button class="tourlink tourlink_no_linkedin" type="button">No</button></div>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>Your job title <span class="required-star">&nbsp; *</span></label>
          @if(Session::has('linkedin_job_title'))
            @php $job_title = Session::get('linkedin_job_title'); @endphp
          @else
            @php $job_title = Session::get('space_info')['space_user'][0]->metadata['user_profile']['job_title']??''; @endphp
          @endif
          <textarea id="jobtitletxt" class="form-control jobtitle_admin linked" placeholder="e.g. Procurement manager" name="job_title" >{{ $job_title }}</textarea>
          <div class="admin_job_error">
            <span class="jobtitleerror" style="display:none"></span>
            @if ($errors->has('job_title'))
            <span class="error-msg text-left">
             {{ $errors->first('job_title') }}
           </span>
           @endif
          </div>
        </div>
        @if(Session::get('space_info')['sub_companies'] == '1' && (isset(Session::get('space_info')['space_user'][0]['sub_comp']['company_name']) && Session::get('space_info')['space_user'][0]['sub_comp']['company_name']!='' ))
        <div class="form-group">
          <label>Community <span class="required-star">&nbsp; *</span></label>
          <input  type="text" name="company_name" class="form-control" readonly="readonly"  placeholder="Job title" value="@if(Session::get('space_company')!=''){{Session::get('space_company')['company_name']??''}}@endif">
          <input  type="hidden" name="company" class="form-control"  placeholder="Job title" value="@if(Session::get('space_company')!=''){{Session::get('space_company')['id']??''}}@endif">
        </div>
        <div class="form-group sub_comp_div">
          <label>Company <span class="required-star">&nbsp; *</span></label>
          <input id="comptext" type="text" class="form-control sub_comp_input" name="sub_comp" placeholder="Start typing to add your company" value="@if(Session::get('space_info')['space_user'][0]['sub_comp']['company_name']!='' && strpos(Session::get('space_info')['space_user'][0]['sub_company_id'], '00000000') === false){{Session::get('space_info')['space_user'][0]['sub_comp']['company_name']  ??''}}@endif"  autocomplete="off">
          <div id="suggesstion-box"></div>
          <div class="admin_company_error">
            <span class="comptexterror" style="display:none"></span>
            @if ($errors->has('sub_comp'))
            <span class="error-msg text-left">
             {{ $errors->first('sub_comp') }}
           </span>
           @endif
          </div>
        </div>

        @else
        <div class="form-group">
          <label>@if(Session::get('space_info')['sub_companies'] == '1')Community @else Company @endif<span class="required-star">&nbsp; *</span></label>
          @if( isset($checkBuyer) && $checkBuyer == 'buyer')
          <input id="comptext" type="text" name="company_name" class="form-control" readonly="readonly" placeholder="Job title" value="@if(Session::get('space_company')!=''){{Session::get('space_company')['company_name']??''}}@endif">
          <input id="comptext" type="hidden" name="company" class="form-control"  placeholder="Job title" value="@if(Session::get('space_company')!=''){{Session::get('space_company')['id']??''}}@endif">
        </div>
        @if(Session::get('space_info')['sub_companies'] == '1')
        <div class="form-group sub_comp_div">
        <label>Company <span class="required-star">&nbsp; *</span></label>
 
        <input id="comptext" type="text" class="form-control sub_comp_input" name="sub_comp" placeholder="Start typing to add your company" value="@if(Session::get('space_company')!='' && (strpos(Session::get('space_info')['space_user'][0]['sub_company_id'], '00000000') === false ||strpos(Session::get('space_info')['space_user'][0]['sub_company_id'], '00000000') === 0) ){{Session::get('space_company')['company_name']??''}}@endif"  autocomplete="off">

        <div id="suggesstion-box"></div>
        <div class="admin_company_error">
          <span class="comptexterror" style="display:none"></span>
          @if ($errors->has('sub_comp'))
          <span class="error-msg text-left">
           {{ $errors->first('sub_comp') }}
         </span>
         @endif
        </div>
      </div>
      @endif
    @else
      <div class="form-group">
       <input id="comptext" type="text" name="company_name" class="form-control" readonly="readonly" placeholder="Job title" value="@if(Session::get('space_company')!=''){{Session::get('space_company')['company_name']??''}}@endif">
       <input id="comptext" type="hidden" name="company" class="form-control"  placeholder="Job title" value="@if(Session::get('space_company')!=''){{Session::get('space_company')['id']??''}}@endif">
      </div>
    @endif
  @endif
  <input type="hidden" name="space_id" class="hidden_sp_id"  value="{{Session::get('space_info')['id']}}">
  @if(isset(Session::get('space_info')['space_user'][0]->metadata['user_profile']))
  @if (!empty(Session::get('space_info')['space_user'][0]->metadata['user_profile']['bio']))
     @php $bio_length = strlen(Session::get('space_info')['space_user'][0]->metadata['user_profile']['bio']) ; @endphp
    @elseif(session('linked'))
    @php  $bio_length = strlen($account_data->linkedin->user->headline??''); @endphp
    @else
     @php $bio_length = 0; @endphp
  @endif
  @endif
  <div class="form-group biotextarea">
    <label>Bio</label>
    <textarea id="biotextarea" placeholder="How would you describe your responsibilities?" class="form-control linked" name="bio" maxlength="300"  onfocus="textAreaAdjust(this)" onkeyup="countChar(this)" >{{Session::get('linkedin')['biotext']??Session::get('space_info')['space_user'][0]->metadata['user_profile']['bio']??''}}</textarea>
    <span class="letter-count">
      <div id="charNum1"  val="{{$bio_length??0}}">{{$bio_length??0}}</div>
    /300
    </span>
    <div class="pf_bio" style="display: none;">
      <div class="popover tour bio_blue_popup bio_blue_popup_linkedin pf_bio" style="display: none !important">
        <div class="arrow_box_wrap"><span class="arrow_box"></span></div>
        <h3 class="popover-title">Would you like to edit your Bio?</h3>
        <div class="popover-content">Your Bio is specific to each Client Share so can be tailored to each relationship.</div>
        <div class="popover-navigation"><button class="tourlink tourlink_yes">Yes</button><button class="tourlink tourlink_no">No</button></div>
      </div>
    </div>
  </div>
  <h3 class="modal-title contact-info" id="myModalLabel">Contact information</h3>
  <div class="form-group">
    @if(Session::has('linkedin_link'))
        @php $linkedin_l = Session::get('linkedin_link'); @endphp
     @else
        @php $linkedin_l = Auth::User()['contact']['linkedin_url']??''; @endphp
     @endif
    <label>LinkedIn profile</label>
    <input type="text" class="form-control linked" placeholder="Paste your LinkedIn profile here"  id='linkedin_link' name="user[contact][linkedin_url]" value="{{$linkedin_l}}">
  </div>
  <div class="form-group">
    <label>Email address</label>
    <input type="text" class="form-control" readonly="readonly" placeholder="e.g. Procurement manager" value="{{Auth::User()->email}}">
  </div>
  <div class="form-group">
    @if(Session::has('linkedin_phoneno'))
        @php $contact_no = Session::get('linkedin_phoneno'); @endphp
     @else
        @php $contact_no = Auth::User()['contact']['contact_number']??''; @endphp
     @endif
    <label>Phone number</label>
    <input type="text" maxlength="24" class="form-control ph_number" placeholder="Enter your phone number here" name="user[contact][contact_number]" onfocus="this.value = this.value;" value="{{$contact_no}}" autocomplete="off">
  </div>
  <div class="form-group">
    <div class="required-fields">*Required fields</div>
    <button class="btn btn-primary right save_user_profile">Update</button>
    @if ($errors->has('user.first_name') || $errors->has('user.last_name') || $errors->has('user.contact.contact_number'))
    <script> $('#temp_id_trigger').trigger('click'); </script>
    @endif
  </div>
</div>
</form>
</div>
<div class="modal-footer"></div>
</div>
<!-- white popoup -->
</div>
</div>
<input type="hidden" value="{{Session::get('space_info')['id']}}" name="share_id" class="hidden_space_id">
<input type="hidden" value="{{Auth::user()->id}}" class="notify_userid">
<input type="hidden" value="{{ Session::get('space_info')['id'] }}" class="notify_spaceid">

<script>

  /*SCROLL NOTIFICATIPN start*/
  jQuery(function($){
    $('.notifications').bind('scroll', function(){
      if($(this).scrollTop() + $(this).innerHeight()>=$(this)[0].scrollHeight){
        $('.notification_limit').val(parseInt($('.notifications li').length));
        $('.notification_limit_more').val('6');
        $('.notification_offset_more').val($('.notifications li').length);
        new_limit = $('.notification_limit_more').val();
        new_offset = $('.notification_offset_more').val();
        var notification_header = '0';
        var loggeduserid = $('.notify_userid').val();
        var loggedspaceid = $('.notify_spaceid').val();

        $.ajax({
          type: "GET",
          url: '{{ url('/',[],env("HTTPS_ENABLE", true)) }}'+'/activity_notification?loggedid='+loggeduserid+'&space_id='+loggedspaceid+'&limit='+new_limit+'&offset='+new_offset+'&notification_header='+notification_header,
          beforeSend : function()    {      },
          success: function (response) {
          //alert(response);
          $('#loading_li').remove();
          $('.notifications').append(response);
          },
          error: function (message) { }
        });
      }
    })
  });
     

  function countChar(val) {
    var len = val.value.length;
    var cnt = $('#charNum1').val();
    $('#charNum1').text(cnt + len);
  }

  function fillLinkedinData(){
    if($('#jobtitletxt').val() ===''){
      $('#jobtitletxt').val('{{$account_data->linkedin->user->positions->values[0]->title??''}}');
    }
    if($('#biotextarea').val() ===''){
      $('#biotextarea').val('{{$account_data->linkedin->user->headline??''}}');
    }
    if($('#linkedin_link').val() ===''){
      $('#linkedin_link').val('{{$account_data->linkedin->user->publicProfileUrl??''}}');
    }
  }

  function validateProfileForm() { 
    var isValid = true;
    $('.linked').each(function() {
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
      $('.pf_bio').hide();
    }
  }

  function textAreaAdjust(o) {
    o.style.height = "1px";
    o.style.height = (1+o.scrollHeight)+"px";
  }

  autosize(document.querySelectorAll('textarea#jobtitletxt'));
  autosize(document.querySelectorAll('textarea#biotextarea'));
  $.fn.focusEnd = function() {
    $(this).focus();
    var tmp = $('<span />').appendTo($(this)),
    node = tmp.get(0),
    range = null,
    sel = null;

    if (document.selection) {
      range = document.body.createTextRange();
      range.moveToElementText(node);
      range.select();
    } else if (window.getSelection) {
      range = document.createRange();
      range.selectNode(node);
      sel = window.getSelection();
      sel.removeAllRanges();
      sel.addRange(range);
    }
    tmp.remove();
    return this;
  }

  $('.close').click(function(){
    $('.profile_update_form').trigger("reset");
    $('#blah55').attr('style','');
  });

  $(document).on('click', '.profile_popup', function() {
    validateProfileForm();
    var leng = $('.jobtitle_admin').val().length;
    var er = { "color": "#ff5252",
    "font-size": "12px",
    "letter-spacing": "0",
    "line-height": "12px",
    "margin-bottom": "9px",
    "margin-top": "9px" };
         //alert(char);
    if( leng >= 1){
      $('.jobtitleerror').hide();
    }else{
      $('.jobtitleerror').css(er).html('Field is Required').show();
      return false;
    }
  });

  $(document).on('click','#temp_id_trigger',function(){
    /* log home page visit */
    custom_logger({
      'description' : 'view profile',
      'action' :'view profile'
    });
    $('#user_profile').addClass('in');
    $('#user_profile').show();
    $('#biotextarea').trigger('onfocus');
    $('#user_profile').css('background','rgba(0, 0, 0, 0.7) none repeat scroll 0 0');
    $('body').addClass('overflowhidden');
    $('#user_profile').addClass('popup-overflow');
  });

  $(document).on('click','.close',function(){
    $('#user_profile').removeClass('in');
    $('#user_profile').hide();
    $('body').removeClass('overflowhidden');
    $('#user_profile').removeClass('popup-overflow');
  });

  $(document).on('click', '.save_user_profile', function() {
    var char = $('#jobtitletxt').val().length;
    var comptext = $('#comptext').val().length;
    var namefirsttext = $('#first_name').val().length;
    var namelasttext = $('#last_name').val().length;
    var er = { "color": "#ff5252",
      "font-size": "12px",
      "letter-spacing": "0",
      "line-height": "12px",
      "margin-bottom": "4px",
      "margin-top": "7px",
      "display": "inline-block" };
    if(char < 1){
      $('.jobtitleerror').css(er).html('Field is Required').show();
      return false;
    }else if(comptext < 1){
      $('#suggesstion-box').hide();
      $('.comptexterror').css(er).html('Field is Required').show();
      return false;
    }else if(namefirsttext < 1){
      $('.first_name_text_error').css(er).html('Field is Required').show();
      return false;
    }else if(namefirsttext > 25){
      $('.first_name_text_error').css(er).html('The first name cannot be greater than 25 characters').show();
      return false;
    }else if(namelasttext > 25){
      $('.last_name_text_error').css(er).html('The last name cannot be greater than 25 characters').show();
      return false;
    }else if(namelasttext < 1){
      $('.last_name_text_error').css(er).html('Field is Required').show();
      return false;
    }else{
      return true;
    }
  });


  $(document).ready(function(){
    <?php if(Session::has('linked')){ ?>
      fillLinkedinData();
      $('#temp_id_trigger').trigger('click');
      $('.pf_bio').show();
      $(".profile_linkedin_wrap").hide();
      $(".linked_hide_overlay").hide();
      $('.linkedinbtn').hide();
      return false;
    <?php } ?>

    var loggeduserid = $('.notify_userid').val();
    var loggedspaceid = $('.notify_spaceid').val();
    notifications_trigger();
    var notification_run = setInterval(function() {
      notifications_trigger();
    }, <?php echo Config::get('constants.AJAX_INTERVAL')?>);

    function notifications_trigger(){
      
      activityNotification();
      notificationCount();
      getAllShareNotifications();
    }  
    /*SCROLL NOTIFICATIPN start*/
    jQuery(function($){
      $('.notifications').bind('scroll', function(){
        if($(this).scrollTop() + $(this).innerHeight()>=$(this)[0].scrollHeight){
          $('.notification_limit').val(parseInt($('.notifications li').length));
          $('.notification_limit_more').val('6');
          $('.notification_offset_more').val($('.notifications li').length);
          new_limit = $('.notification_limit_more').val();
          new_offset = $('.notification_offset_more').val();
          var notification_header = '0';
          var loggeduserid = $('.notify_userid').val();
          var loggedspaceid = $('.notify_spaceid').val();

          $.ajax({
            type: "GET",
            url: '{{ url('/',[],env("HTTPS_ENABLE", true)) }}'+'/activity_notification?loggedid='+loggeduserid+'&space_id='+loggedspaceid+'&limit='+new_limit+'&offset='+new_offset+'&notification_header='+notification_header,
            beforeSend : function()    {      },
            success: function (response) {
            //alert(response);
            $('#loading_li').remove();
            $('.notifications').append(response);
            },
            error: function (message) { }
          });
        }
      })
    });
        /*SCROLL NOTIFICATIPN end*/
    $(document).on('click', '.feedbacknoti', function() {
      if($(".feedbackbtn").length == 0) {
        var redirect = baseurl+'/clientshare/{{ Session::get("space_info")["id"] }}?feedback=true';
        window.location = redirect;
      }
    });

    $(document).on('keyup', '#jobtitletxt', function(){
      var char = $('#jobtitletxt').val().length;
      if( char > 0){ //alert('d');
        $(".jobtitleerror").css("display", "none");
      }
    });

    $(document).on('keyup', '#comptext', function() {
      var char = $('#comptext').val().length;
      if( char > 0){ 
        $(".comptexterror").css("display", "none");
      }
    });

    $('.ph-number').bind('keyup paste', function(){
      position = this.selectionStart;
      this.value = this.value.replace(/[^ 0-9+(),-.]/g, '');
      this.selectionEnd = position;
    });

    autosize(document.querySelectorAll('textarea.txtarea'));
    window.setTimeout(function() {
      $(".flash-sucess").fadeTo(1500, 0).slideUp(500, function(){
        $(this).remove();
      });
    }, 3000);
  });

  
  $(document).ajaxStart(function (e) {
    e.preventDefault();
    var space_id = $('.hidden_space_id').val();
    if(!check_share_active || check_share_active.state() === 'resolved'){
      check_share_active = $.ajax({
        type: "GET",
        url: baseurl+'/check_space_deleted?space_id='+space_id,
        success: function(response) {
          if(response != '') {
            window.location.href = baseurl+response;
          }
        },
        error: function(message) { console.log('Error: Please refresh the page');  }
      });
    }
  });

    /*Save Share after Editing*/
  $(".edit_share_save_btn").on('click', function(e) {
    var space_id= $(".hidden_space_id").val();
    var Share = $.trim($(".updated_share").text());
    var update_share = encodeURIComponent(Share);
    if(update_share.length > 90){
        var update_share = update_share.substring(0,90);
    }
    $('.edit_share_save_btn').prop('disabled','true');
    if(update_share != ''){
      $.ajax({
        type: "GET",
        url: baseurl+'/update_admin_share?spaceid='+space_id+'&sharename='+update_share,
        success: function(data) {
          $('.edit_share_save_btn').prop('disabled',false);
          location.reload();
        },
        error: function(error) {
          console.log(error);
        }
      });
    }else{
      alert('Share Name can not be empty.')
    }
  });
</script>


@if(isset($checkBuyer) && isset($feedback_status))
<script type="text/javascript">
  $(document).ready(function(){
    var check_buyer =  '{{$checkBuyer}}';
    var feedback_status = {{ $feedback_status['feedback_status']??0 }}
    var current_month_eligible = {{$feedback_status['current_month_eligible']?1:0}};
    var user_current_quater_feedback = {{sizeOfCustom($feedback_status['user_current_quater_feedback'])}};
    if(check_buyer == 'buyer' && feedback_status && current_month_eligible && !user_current_quater_feedback){
      @if( !is_null(app("request")->input("feedback_rating")) )
        $('#feedback-popup').find('input:radio[value={{app("request")->input("feedback_rating")}}]').trigger('click');
      @endif
      @if(Auth::user()->show_tour)
        show_feedback = true;
      @else
        $('#feedback-popup').modal('show');
      @endif
    }
  });
</script>
@endif

