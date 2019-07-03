time = 5*60;
seconds_limit = 60;
var code_verification_limit = 3;
var password_min_length = 8;
var password_max_length = 60;
var name_max_length     = 25;

function ValidateIPaddress(ipaddress) {
  if (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ipaddress)){
    return (true)
  }
  return (false)
}

function verifyCode(){
  if(!(--code_verification_limit)) return $(".user_register_from").submit();
  $.ajax({
    type: "POST",
    url: baseurl+'/verify_user_register_code',
    data: $('.user_register_from').serialize(),
    beforeSend: function(){
      $('.user_register_form_submit').text('Processing...').attr('disabled', true);
    },
    success: function (response) {
      console.log(code_verification_limit);
      if(!response.is_match){
        $('input[name="verify_code"]').val('').focus();
        $('span.verify-code-error').html('The verification code you entered is incorrect.');
      } else {
        $(".user_register_from").submit();
      }
    },
    complete: function(){
      $('.user_register_form_submit').text('Register').removeAttr('disabled');
    }
  });
}
function removeTimer() {
  $.removeCookie('verification_timer');
  $.removeCookie('verification_time_set');
}

window.onbeforeunload = function(){
  removeTimer();
}

$( document ).ready(function() {

  $(document).on('blur, keyup', '.ip-address-block input', function(){
      if(!ValidateIPaddress($(this).val())){
        $('button.modal_initiate_btn').attr('disabled', true);
        $(this).parent().find('.ip-error-msg').remove();
        $(this).parent().append('<span class="left ip-error-msg" style="color: rgb(255, 82, 82); font-size: 12px;">Please enter valid IP address</span>');
      } else {
        $(this).parent().find('.ip-error-msg').remove();
        if(!$('.ip-error-msg').length){
          $('button.modal_initiate_btn').removeAttr('disabled');
        }
      }
  });

  $(document).on('click', '.add-ip-address', function() {
    block = $('.ip-address-block').eq(0).clone();
    block.find('input').val('');
    console.log($('.ip-address-block').length-1);
    $('.ip-address-block').eq($('.ip-address-block').length-1).after(block);
  });

  $(document).on('click', '.remove-ip-address', function() {

    if($('.ip-address-block').length==1) {
      return swal({
        title: 'Turn off IP-restriction',
        text: 'Would you like to turn this feature off?',
        buttons: {
          cancel: true,
          confirm:{
            text: 'Yes',
            className: 'turn-off-ip-restriction btn-default',
            closeModal: false
          },
        },
      });
      return;
    }

    $(this).closest('.ip-address-block').remove();
  });

  $(document).on('click', '.ip-restriction-toggle', function(){

    $('.ip-address-block').toggle($(this).prop('checked'));
    if($(this).prop('checked')) {

    }
  });

  $(document).on('click', '.turn-off-ip-restriction', function() {
    $('.ip-restriction-toggle').attr('checked', false);
    $('.ip-address-block, .add-ip-address').remove();
    swal.close();
  });

  var current_date = new Date();
  var current_time_stamp  = current_date.getTime();
  var previous_time_stamp = $.cookie('current_time_stamp');
  var difference_in_time_stamp = current_time_stamp - previous_time_stamp;
  var difference_in_seconds = parseInt((difference_in_time_stamp/1000));
  difference_in_seconds = difference_in_seconds - parseInt(5);

  if(difference_in_seconds > seconds_limit) {
    $.removeCookie('current_time_stamp');
    $.removeCookie('visited');
    removeTimer();
  }

  if($.isNumeric(difference_in_seconds)){
    seconds_limit=(parseInt(seconds_limit)-parseInt(difference_in_seconds));
  }

  var yet_visited = $.cookie('visited');
  var verification_timer = $.cookie('verification_timer');
  var text = $('.error-msg').text();

  if(verification_timer > 0) {
    time = $.cookie('verification_time_set');
    onVerify();
  }
  if($('#code').length) onVerify();

  if(yet_visited > 0) {
    onTimer();
  }

  if (text.match(/[0-9]/i)) {
    onTimer();
  }

  $("#code").on('change, paste input', function(){
    var value = $(this).val();
    if(value.length>0){
          $("#show-sent").prop('disabled', false);
    }else{
      $("#show-sent").prop('disabled', true);
    }
  });
  $(".bluelinks").on('click',function(){
    removeTimer();
  });
  $(".check_box_error").hide();
  $(document).on("click", ".checkd", function(e) {
    $(".check_box_error").hide();
  });

  
  $('.verify-user-details').click(function(){
    $.ajax({
        type: "POST",
        url: baseurl + '/verify_user',
        data: {
            'email': $('.user_register_from input[name=email]').val(),
            'space_id': $('.user_register_from input[name=shareid]').val()
        },
        success: function (response) {
                $('.verify-user-details').hide();
                $('.user_register_form_submit').toggle(100);
                $('.verify-code').toggle(100);
                $('.registration-verification p').html('Please check your inbox for your verification code.').parent().show();
            
        },
        error: function (reject) {
            if(reject.status==422){
                   var errors = reject.responseJSON.errors;
                    $.each(errors, function (key, val) {
                        $("#register_"+ key + "_error").text(val);
               });
            }else{
                $('.registration-verification p').html('Something went wrong, please try again.').parent().show();
            }
         }
    });

  });
});

function onVerify() {
  $('#timer').show();
  var minutes = Math.floor( time / seconds_limit );
  if (minutes < 10) minutes = "0" + minutes;
  var seconds = time % seconds_limit;
  if (seconds < 10) seconds = "0" + seconds; 
  var text = minutes + ':' + seconds;
   $('#timer').text("Verification code sent on your email, Please Verify in "+text+" seconds");
  time--;
  if (time >= 0) {
     $.cookie('verification_timer', minutes);
     $.cookie('verification_time_set', time);
    setTimeout(onVerify, 1000);
  }else{
    removeTimer();
    window.location = baseurl + "/";
    $('#timer').hide();
  }
}

$(document).on("click", ".close_btn", function(e) {
  $('.login_error_message').hide();
});

function onTimer() {
  $('.timeout-message').show();
  var date = new Date();
  var current_time_stamp = date.getTime();
  var is_current_time_stamp_added = $.cookie('current_time_stamp');
  if(!is_current_time_stamp_added) {
    $.cookie('current_time_stamp', current_time_stamp);
  }
  $('.timeout-message .text-center').text("Too many incorrect login attempts. Please try again in "+seconds_limit+" seconds");
  seconds_limit--;
  $('.error-msg').hide();
  if (seconds_limit >= 0) {
    $.cookie('visited', seconds_limit);
    setTimeout(onTimer, 1000);
    $('button').prop('disabled', true);
  }else{
    $.cookie('visited', 0);
    $('.timeout-message').hide();
    $('.error-msg').hide();
    $("div").removeClass("has-error");
    $('button').prop('disabled', false);
  }
}

$(document).on("click", ".user_register_form_submit", function(e) {
  e.preventDefault();
  var new_password = $('#password').val();
  var confirm_password = $('#password_confirmation').val();
  var first_name = $('#firstname').val();
  var last_name = $('#lastname').val();
  var validate_new_password =  true;
  var validate_confirm_password =  true;
  var validate_firstname =  true;
  var validate_lastname =  true;
  var validate_all_values =  true;
  $('.login_error_message p').hide(100);
  if(new_password && confirm_password && first_name && last_name){
    if(new_password.length < password_min_length || new_password.length > password_max_length)
       validate_new_password = false;
    if(confirm_password.length < password_min_length || confirm_password.length > password_max_length)
       validate_confirm_password = false;
    if(first_name.length <1 || first_name.length >name_max_length)
       validate_firstname = false;
    if(last_name.length <1 || last_name.length >name_max_length)
       validate_lastname = false;
    if(!/\d/.test(new_password))
       validate_new_password = false;
    if(!/[a-z]/.test(new_password))
       validate_new_password = false; 
    if(!/[A-Z]/.test(new_password))
       validate_new_password = false;
    if(!/[0-9]/.test(new_password))
       validate_new_password = false;
    if(!/\d/.test(confirm_password))
       validate_confirm_password = false;
    if(!/[a-z]/.test(confirm_password))
       validate_confirm_password = false; 
    if(!/[A-Z]/.test(confirm_password))
       validate_confirm_password = false;
    if(!/[0-9]/.test(confirm_password))
       validate_confirm_password = false;

    if(!validate_new_password){
      $('.password_error').html('Your password must be a minimum of '+password_min_length+' characters and maximum of '+password_max_length+' characters and contain an upper case letter, lower case letter and a number');
      validate_all_values = false;
    }else{
      $('.password_error').html('');
    }
    if(!validate_confirm_password){
      $('.confirm_password_error').html('Your password must be a minimum of '+password_min_length+' characters and maximum of '+password_max_length+' characters and contain an upper case letter, lower case letter and a number');
      validate_all_values = false;
    }else{
      $('.confirm_password_error').html('');
    }
    if(!validate_firstname){
      $('.firstname_error').html('First name cannot be greater than '+name_max_length+' characters');
      validate_all_values = false;
    }else{
      $('.firstname_error').html('');
    } 
    if(!validate_lastname){
      $('.lastname_error').html('Last name cannot be greater than '+name_max_length+' characters');
      validate_all_values = false;
    }else{
      $('.lastname_error').html('');
    }    
    if(new_password != confirm_password){
      $('.confirm_password_error').html('Password & Confirm Password must be same');
      validate_all_values = false;
    }    
  }
  if(!new_password){
      $('.password_error').html('Password is required');
      validate_all_values = false;
  }
  if(!first_name){
      $('.firstname_error').html('First name is required');
      validate_all_values = false;
  }
  if(!last_name){
      $('.lastname_error').html('Last name is required');
      validate_all_values = false;
  }
  if(!confirm_password){
      $('.confirm_password_error').html('Confirm password is required');
      validate_all_values = false;
  }
  if(!$('input[name="verify_code"]').val().length){
    $('.verify-code-error').html('Verification code is required');
      validate_all_values = false;
  }

  if($('.uncheck').is(':hidden')){
    $(".check_box_error").show();
    validate_all_values = false;
  }
  
  if(validate_all_values){
    verifyCode();
  }else{
    return false;
  }
});

$(document).on("click", ".reset_pass", function(e) {
  e.preventDefault();  
  var email = $('#email').val();
  var new_password = $('#password').val();
  var confirm_password = $('#confirm_password').val();
  var validate_new_password =  true;
  var validate_confirm_password =  true;
  var validate_all_values =  true;
  if(email.length > 0){
      $('.email_error').html('');
  }
  if(email && new_password && confirm_password){
    if(new_password.length < password_min_length || new_password.length > password_max_length)
      validate_new_password = false;
    if(confirm_password.length < password_min_length || confirm_password.length > password_max_length)
      validate_confirm_password = false;
    if(!/\d/.test(new_password))
      validate_new_password = false;
    if(!/[a-z]/.test(new_password))
      validate_new_password = false; 
    if(!/[A-Z]/.test(new_password))
      validate_new_password = false;
    if(!/[0-9]/.test(new_password))
      validate_new_password = false;
    if(!/\d/.test(confirm_password))
      validate_confirm_password = false;
    if(!/[a-z]/.test(confirm_password))
      validate_confirm_password = false; 
    if(!/[A-Z]/.test(confirm_password))
      validate_confirm_password = false;
    if(!/[0-9]/.test(confirm_password))
      validate_confirm_password = false;
    if(!validate_new_password){
      $('.password_error').html('Your password must be a minimum of '+password_min_length+' characters and maximum of '+password_max_length+' characters and contain an upper case letter, lower case letter and a number');
      validate_all_values = false;
    }else{
      $('.password_error').html('');
    }
    if(!validate_confirm_password){
      $('.confirm_password_error').html('Your password must be a minimum of '+password_min_length+' characters and maximum of '+password_max_length+' characters and contain an upper case letter, lower case letter and a number');
      validate_all_values = false;
    }else{
      $('.confirm_password_error').html('');
    }   
    if(new_password != confirm_password){
      $('.confirm_password_error').html('Password & Confirm Password must be same');
      validate_all_values = false;
    }    
  }
  if(!email){
    $('.email_error').html('Email is required');
    validate_all_values = false;
  }
  if(!new_password){
    $('.password_error').html('Password is required');
    validate_all_values = false;
  }   
  if(!confirm_password){
    $('.confirm_password_error').html('Confirm password is required');
    validate_all_values = false;
  }     
  if(validate_all_values){
    $(".reset_password_form").submit();
  }else{
    return false;
  }
});