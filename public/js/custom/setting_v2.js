function cleanUrl(selected_tab){
    var tab_name = selected_tab;
    var old_url = window.location.href;
    var index_of_character = 0;
    var new_url = old_url;
    index_of_character = old_url.indexOf('?');
    if(index_of_character == -1){
      index_of_character = old_url.indexOf('#');
    }
    if(index_of_character != -1){
      new_url = old_url.substring(0, index_of_character);
    }
    if(getParameterByName('tab_name')){
      old_url = tab_name = '#'+getParameterByName('tab_name');
    }
    window.history.pushState("", "", new_url+tab_name);
    if(old_url == tab_name) $('a[href="'+tab_name+'"]').trigger('click');
}
/**/
function reset_bulk_invitation(){
  bulk_invitation = new Array();
  $('.bulk-invitation-progress-info').empty();  
  $('.bulk-email-trigger').attr('disabled', true);
}

function addEditOpenedElementValidation(message = "") {
  var add_domain_btn = $('.form_field_section .domain-action-btn:visible');
  if (add_domain_btn.length !== 0) {
    var last_input = $(document).find('.dropdown-save-btn:visible').parents('.domain-input-grp').find('.domain_name_inp')
    if(last_input.length == 0){
      var length = $('.form_field_section').find('.domain-input-grp').length-1;
      last_input = $('.form_field_section').find('.domain-input-grp').eq(length).find('input');
    }
    message = (message == "") ? 'Please save or cancel changes in this domain first.' : message;
    if (!last_input.siblings('.error-msg').length !== 0) {
      last_input.after('<span class="error-msg text-left">' + message + '</span>').parent().addClass("has-error") 
    }else{
      last_input.siblings('.error-msg').text(message);
    }
    return false;
  }
  return true;
}

/**/
function initiate_bulk_invitation(){
  $('.bulk-email-trigger').focus();
}

function bulk_invitation_preview(){
  $('.bulk-invitation-progress-info').html('<label class="uploaded-document">'+(window[direct_upload_s3_data[0]['storage']])[0]['originalName']+'<img src="'+baseurl+'/images/loading_bar1.gif"></label>');

  $.ajax({
    type: 'POST',
    url: 'bulk_invitations',
    headers: {
      'cache-control': 'no-cache',
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    data: $('.bulk-invitation-form').serialize(),
    success: function (response) {
      if(!response) return false;
      if(response.error){
        reset_bulk_invitation();
        alert(response.message);
        return false;
      }
      var response_length = response.length;
      $('.file-preview').empty();
      for (var increment = 0, length_limit = response_length; increment < length_limit; increment++) {
        html = '<div class="data-wrap">';
        html += '<div class="first-name-wrap">'+response[increment].first_name;
        if(response[increment].status.first_name)
          html +='<div class="error-msg">'+response[increment].status.first_name+'</div>';
        html += '</div>';
        
        html += '<div class="last-name-wrap">'+response[increment].last_name;
          if(response[increment].status.last_name)
            html += '<div class="error-msg">'+response[increment].status.last_name+'</div>';
        html += '</div>';

        html += '<div class="email-wrap">'+response[increment].email;
          if(response[increment].status.email)
            html += '<div class="error-msg">'+response[increment].status.email+'</div>';
        html += '</div>';
        html += '</div>';
        $('.file-preview').append(html);
      }
      $('input[name=finalized_data]').val(JSON.stringify(response));
      $('#bulk-upload-status').modal();
      $('.bulk-email-trigger').attr('disabled', false);
      /* remove processing animation */
      $('.bulk-invitation-progress-info').find('img').remove();      
    }
  });
}

function invitationFileTrigger() {
  bulk_invitation = new Array();
  $('.bulk-email-trigger').attr('disabled', true);
  $('.bulk-invitation-progress-info').empty();
  if($('.s3_running_process').length) return false;
  direct_upload_s3_data.push({
    'storage': 'bulk_invitation',
    'progress_element_class': 's3_progress',
    'form_field_class': 'bulk-invitation-file',
    'done_callback': 'bulk_invitation_preview',
    // 'error_callback': 'upload_executive_file_error',
    'allowed_extension': ['csv'],
    'progress_bar_ele': '.bulk-invitation-progress-info'
  });
  $('#upload_s3_file').trigger('click');
}

function send_mail_setting(btn) {
  var spaceid = $(".spaceid").val();
  var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
  $('.white_box_info').hide();
  var mail_container = $(btn).parent().parent();
  $(btn).attr('disabled', true);
  $(btn).html('Please wait...');
  $(btn).append('<i class="fa fa-circle-o-notch fa-spin" aria-hidden="true" ></i>');
  mail = {
    greetings: $('.mailbody').find('span').eq(0).html(),
    name: $('.mailbody').find('span').eq(1).html(),
    message: "\n\n\n" + $('.mail_body:visible').val(),
    regards_head: "\n\n\n" + $('.mailbody').find('div').eq(0).html(),
    sender: "\n" + $('.mailbody').find('div').eq(1).html(),
    company_nmae: "\n" + $('.mailbody').find('div').eq(2).html()
  };
  var settings = {
    "async": true,
    "crossDomain": true,
    "url": baseurl+"/invite_user",
    "method": "post",
    "headers": {
        "cache-control": "no-cache",
        'X-CSRF-TOKEN': CSRF_TOKEN
    },
    "data": {
      "resend_mail": 1,
      "share_id": spaceid,
      "user": {
        "first_name": $(mail_container).find("input[name=first_name]").val(),
        "last_name": $(mail_container).find("input[name=last_name]").val(),
        "email": $(mail_container).find("input[name=email]").val(),
        "subject": $(mail_container).find('.subjectbody').find('span').html()
      },
      "mail": {
        "to": $(mail_container).find("input[name=email]").val(),
        "body": mail
      }
    }
  }

  if( !$(btn).hasClass('resend_invite_btn') )
    delete settings.data.resend_mail;

  $.ajax(settings).done(function(response) {
    if (response.code == 0) {
      $(mail_container).find('.white_box_info').addClass('success-msg');
      $(mail_container).find('.white_box_info').removeClass('error-message warning-message');
      $(mail_container).find('.white_box_info').html("Invitation sent successfully");
      $(mail_container).find('.white_box_info').show();
      $(".white-popup").find("input[name=first_name], input[name=last_name], input[name=email]").val("");
      $('.mailbody').find("span").eq(0).hide();
      $('.mailbody').find("span").eq(1).html("");
      localStorage.setItem("Status","Invitation sent successfully");
      setTimeout(function(){
        $('#resendinvites').modal('hide');
        location.reload();},2000);
    }else if (response.code == 400) {
      $(mail_container).find('.white_box_info').removeClass('success-msg');
      $(mail_container).find('.white_box_info').addClass('warning-message');
      $(mail_container).find('.white_box_info').html(response.message);
      $(mail_container).find('.white_box_info').show();
    } else {
      if (response.message['user.first_name']) {
        $(mail_container).find("input[name=first_name]").parent().addClass('has-error');
        $(mail_container).find("input[name=first_name]").parent().find('.error-msg').remove();
        $(mail_container).find("input[name=first_name]").after('<span class="error-msg text-left">' + response.message['user.first_name'] + '</span>');
      } else {
        $(mail_container).find("input[name=first_name]").parent().removeClass('has-error');
        $(mail_container).find("input[name=first_name]").parent().find('.error-msg').remove();
      }
      if (response.message['user.last_name']) {
        $(mail_container).find("input[name=last_name]").parent().addClass('has-error');
        $(mail_container).find("input[name=last_name]").parent().find('.error-msg').remove();
        $(mail_container).find("input[name=last_name]").after('<span class="error-msg text-left">' + response.message['user.last_name'] + '</span>');
      } else {
        $(mail_container).find("input[name=last_name]").parent().removeClass('has-error');
        $(mail_container).find("input[name=last_name]").parent().find('.error-msg').remove();
      }
      if (response.message['user.email']) {
        $(mail_container).find("input[name=email]").parent().addClass('has-error');
        $(mail_container).find("input[name=email]").parent().find('.error-msg').remove();
        $(mail_container).find("input[name=email]").after('<span class="error-msg text-left">' + response.message['user.email'] + '</span>');
      } else {
        $(mail_container).find("input[name=email]").parent().removeClass('has-error');
        $(mail_container).find("input[name=email]").parent().find('.error-msg').remove();
      }
    }
    $(btn).attr('disabled', false);
    $(btn).html('Invite');
  }).error(function() {
    $(mail_container).find('.white_box_info').removeClass('success-msg');
    $(mail_container).find('.white_box_info').addClass('error-message');
    $(mail_container).find('.white_box_info').html("Something went wrong, Try again later!!");
    $(mail_container).find('.white_box_info').show();
    $(btn).attr('disabled', false);
  });
  $(".modal-dialog ").scrollTop(0);
} 

  /* Toggle feedback status */
function toggleFeedbackStatus(feedback_on_of) {
  var space_id = $('.space_id').val();
  var feedback_type = $('#selectFeedbackType').val();
  $.ajax({
    type: 'GET',
    url: baseurl + '/feedback_setting?space_id=' + space_id + '&feedback_on_of=' + feedback_on_of + '&feedback_type=' + feedback_type,
    success: function (response) {
      $('.thankyou-feedback-button').attr('disabled', 'disabled');
      $('.feedback-status-message').show();
      setTimeout(function () {
        $('.feedback-status-message').fadeOut()
      }, 2000);

      window.location.hash = '#feedback-tab';
      location.reload();
    },
    error: function (jqXHR, textStatus, message) {
      if(jqXHR.status === 400){
        var error_msg = '<p class="alert alert-danger text-center error">Invalid input or request! please try again</p>'
        $('.feedback-status-message').after(error_msg);
        setTimeout(function () {
          $('.feedback-status-message').siblings('.alert-danger.error').fadeOut().remove();
        }, 2000);
      }
    }
  });
}

function set_email_rule_in_setting(form_class) {
  $('.domain_name_inp').attr('disabled', false);
  form_to_submit = $("."+form_class);
  if(form_class == 'domain_management_form'){
    $("."+form_class+" .domain_name_inp:hidden").remove();
  }
  both_mail_parent = form_to_submit.parent().parent().parent();
  var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
  var spaceid = $(".spaceid").val();
  var settings = {
    "async": true,
    "crossDomain": true,
    "url": baseurl+"/clientshare/"+spaceid,
    "method": "put",
    "headers": {
        "cache-control": "no-cache",
        'X-CSRF-TOKEN': CSRF_TOKEN
    },
    "data": {
        "metadata": {
            "rule": $(form_to_submit).serializeArray()
        },
        "_method": "put",
    }
  }
  $.ajax(settings).done(function(response) {
    $('.form_field_section .domain_name_inp').attr('disabled', true);
    input = $(form_to_submit).find('input');
    input.parent().find('.error-msg').remove();
    input.parent().removeClass("has-error");
    if (response.code) {
      $.each(response.message, function(index, value) {
        index = index.split(".");
        input = $(form_to_submit).find('input').eq(index[0]);
        input.parent().find('.error-msg').remove();
        input.parent().removeClass("has-error");
        input.attr('disabled', false);
        input.focus();
        addEditOpenedElementValidation(value)
        input.parent().addClass("has-error");
      });
    } else {
      window.location=baseurl+'/setting/'+current_space_id+'#domain-management-tab';
      $(both_mail_parent).find(".white-popup").removeClass('disable');
      var dropdown = $('.form_field_section div.dropdown')
      var toggle_data = dropdown.children('a.dropdown-toggle:hidden')
      if(toggle_data.length > 0){
        toggle_data.show();
        toggle_data.siblings('.dropdown-save-btn').hide();
      }
      var add_domain_btn = $('.form_field_section .add-domain-btn').last();
      if(add_domain_btn.length > 0){
        add_domain_btn.hide();
        add_domain_btn.siblings('div.dropdown').show();
        var domain_name = add_domain_btn.siblings('input.domain_name_inp').val();
        add_domain_btn.parent('.domain-input-grp').attr('data-id',btoa(domain_name))
      }
    }
  });
}

function menu_pre_select() { 
    if ($(window).width() < 768 && !window.location.hash) {
          return $('.setting_tabs li:first-child').find('a').attr('aria-selected',false).removeClass('active');
    }
     
    if (window.location.hash) {
        var hash = $(location).attr('href').split('#')[1];
        if (hash != '!') {
            return $('a[href="#' + hash + '"]').trigger('click');
        }
    }
    return $('.setting_tabs li:first-child').find('a').trigger('click');
}

$(document).ready(function(){
  setTimeout(function(){$(".session-flash-message").fadeOut()}, 30000);

  $('button.close').click(function() {
    $(this).parent().fadeOut();
  });

  var cancel_invitition="";
  $(".invite-btn,.modelinvite").click(function() {
    autosize(document.querySelectorAll('textarea.comment-area'));
  });

  $(document).on('click', '.post_check_box', function() {
    $(".save_notification_email_status").prop('disabled', false);
    var postcheck = $('.post_check_box').val();
    if(postcheck){
       $('.post_check_box').val('');
    }
    if(!postcheck){
       $('.post_check_box').val('1');
    }
  });

  $(document).on('click', '.comment_check_box', function() {
    $(".save_notification_email_status").prop('disabled', false);
    var commentcheck = $('.comment_check_box').val();
    if(commentcheck){
         $('.comment_check_box').val('');
    }
    if(!commentcheck){
         $('.comment_check_box').val('1');
    }
  });
  $(document).on('click', '.like_check_box', function() {
    $(".save_notification_email_status").prop('disabled', false);
    var likecheck = $('.like_check_box').val();
    if(likecheck){
         $('.like_check_box').val('');
    }
    if(!likecheck){
         $('.like_check_box').val('1');
    }
  });
    $(document).on('click', '.invite_check_box', function() {
    $(".save_notification_email_status").prop('disabled', false);
    var invitecheck = $('.invite_check_box').val();
    if(invitecheck){
         $('.invite_check_box').val('');
    }
    if(!invitecheck){
         $('.invite_check_box').val('1');
    }
  });

    $(document).on('click', '.tag_user_alert', function() {
    $(".save_notification_email_status").prop('disabled', false);
    var tag_user_alert = $('.tag_user_alert').val();
    if(tag_user_alert){
         $('.tag_user_alert').val('');
    }
    if(!tag_user_alert){
         $('.tag_user_alert').val('1');
    }
  });

    $(document).on('click', '.weekly_check_box', function() {
    $(".save_notification_email_status").prop('disabled', false);
    var weeklycheck = $('.weekly_check_box').val();
    if(weeklycheck){
         $('.weekly_check_box').val('');
    }
    if(!weeklycheck){
         $('.weekly_check_box').val('1');
    }
  });

  $(document).on('click', '.tempDateUpdate', function() {
    var tempday =  $('#tempday').val();
    var tempmonth =  $('#tempmonth').val();
    var tempyear =  $('#tempyear').val();
    var space_id = $('.space_id').val();
    $.ajax({
      type: "GET",
      url:  baseurl+'/feedback_tempDate_update?space_id='+space_id+ '&tempyear=' + tempyear+ '&tempmonth=' + tempmonth+ '&tempday=' + tempday,
      success: function(response) {
        location.reload(true);
      },
      error: function(message) {
      }
    });
  });

/* send feedback reminder */   
  $(document).on('click', '.feedback_reminder', function() {
      $.ajax({
         type: "GET",
         url:  baseurl+'/send_feedback_reminder/'+$('.space_id').val(),
         success: function(response) {
            $('.feedback-status-message').html(response);
            $('.feedback-status-message').show();
            setTimeout(function(){$(".feedback-status-message").fadeOut();location.reload(true);}, 1500);
         }, error: function(error) {
            custom_logger({
              'action': 'sending feedback reminder',
              'description': 'An admin sending feedback reminder to all buyers left with feedback submission',
              'metadata': JSON.stringify(error)
            });
         }
      });
  });

/* save Feedback on/ off status*/
  $(document).on('click', '.save_feedback_on_off', function () {
    if ($('#feedback_on_off').prop('checked') == true) {
      toggleFeedbackStatus('TRUE');
    }
  });
    /*** Change  status type then disable button is active ***/
  $(document).on('change', '#selectFeedbackType', function() {
   var feedbackType = $('#selectFeedbackType').val();
    if(feedbackType != ''){
        $('.save_feedback_on_off').prop('disabled', false);
    }else{
        $('.save_feedback_on_off').prop('disabled', true);
    }
   $('.filter-option, .caret').css('color','#424242');
  });

  /*******  feedback enable save button click on checkbox *******/
  $(document).on('click', '#feedback_on_off', function() {
    var saveDisable = $('.thankyou-feedback-button').prop('disabled');
    if (saveDisable) {
       $('.thankyou-feedback-button').prop('disabled', false);
    } else {
       $('.thankyou-feedback-button').prop('disabled', true);
    }
  });

  /*******  Get previous vaule of check box click on the cancel button *******/
  $(document).on('click', '#cancelReload', function() {
        location.reload(true);
  });


  function saveNotificationSettings(){
    var form_data = {};
    $("form[name=email_notification_setting] input").each(function(){
        form_data[this.name] = $(this).val()?$(this).val():0;
    });
    $.ajax({
      type: 'POST',
      headers: {
        "cache-control": "no-cache",
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      url:  baseurl+'/notification_setting',
      data: form_data,
      success: function(response) {
        $('.email_noti_msg').show();
        setTimeout(function(){$(".email_noti_msg").fadeOut()}, 2000);
      },
      error: function(xhr, status, error) {
        errorOnPage(xhr, status, error)
      }
    });
  }

  $(document).on('click', '.save_notification_email_status', function(e) {
    e.preventDefault();
    saveNotificationSettings();
  });

  /*click on promote user */
  $(document).on('click', '.promote_user', function() {
    $('.companyName').show();
    $('.companyNameEdit').hide();
     var userid = $(this).attr('userid');
     var spaceid = $(this).attr('spaceid');
     $('#promote_user').attr('inactive',userid);
     var name = $('.user_nme'+userid).html();
     $('.u_name').html(name);
  });

   /*Promote User on DB */
  $(document).on('click', '#promote_user', function() {
    var userid = $(this).attr('inactive');
    var spaceid = $('.spaceid').val();
    $.ajax({
      type: "GET",
      url:  baseurl+'/promote_admin?spaceid='+spaceid+ '&userid=' + userid,
      success: function(response) {
      },
      error: function(message) {
      }
    });
    $('#promoteuser').modal('hide');
      
      if($(window).width() < 768) {
        $('.user'+userid).find('.member-pic-mobile').append('<span class="admin-text-mobile">A</span>');
      }
      else {
        $('.user_nme'+userid).append(' (admin)');
      }
      $('.user'+userid).find('.promote_user').hide();
      return false;
  });

   /*Add Inactive user id */
  $(document).on('click', '.remove_user', function() {
    $('.companyName').show();
    $('.companyNameEdit').hide();
    var user_id = $(this).attr('userid');
    $('#delete_user').attr('inactive',user_id);
    var name = $('.user_nme'+user_id).html();
    $('.u_name').html(name);
  });

   /*Remove Inactive User */
  $(document).on('click', '#delete_user', function() {
    $('.companyName').show();
    $('.companyNameEdit').hide();
    var inactive_user = $(this).attr('inactive');
    var space_id = $('.spaceid').val();
    $.ajax({
      type: "GET",
      url:  baseurl+'/inactive_user?id='+inactive_user+ '&space_id=' + space_id ,
      success: function(response) {
      },
      error: function(message) {
      }
    });
    $('#removeuserpopup').modal('hide');
    reloadUserManagementPage(space_id);
    $('.user'+inactive_user).hide();
    return false;
  });

  function reloadUserManagementPage(space_id){
    var page = $('.user-management-current-page').val();
    var html = '<li class="page-item" style="display:none"><a class="page-link" href="'+baseurl+'/user_management?space_id='+space_id+'&amp;page='+page+'">'+page+'</a></li>';
    $('.pagination-wrap ul.pagination').append(html);
    $('.pagination .page-item:last a').trigger('click');
  }

  var mouse_is_inside = false;
  $('.shareupdate-wrap,#a_nav').hover(function(){
    mouse_is_inside=true;
  }, function(){
    mouse_is_inside=false;
  });

  $("body").mouseup(function(){
      if(! mouse_is_inside) {
          if($('#bs-example-navbar-collapse-2').hasClass('in')) {
              $("#bs-example-navbar-collapse-2").removeClass("in");
          }

      }
  });

  $(document).on('click','.resend_trigger', function(){
      $('.success-msg').hide();
      main_div = $(this).closest('.tablerow');
      $('#resendinvites').find('input[name=email]').val( $(main_div).find('input[name=email]').val() );
      $('#resendinvites').find('input[name=first_name]').val( $(main_div).find('input[name=first_name]').val() );
      $('#resendinvites').find(".mailbody").find('span').eq(1).html(' ' + $(main_div).find('input[name=first_name]').val() + ',');
      $('#resendinvites').find('input[name=last_name]').val( $(main_div).find('input[name=last_name]').val() );
  });

  $(document).on('click','.cancel_invi_trigger', function(){
    main_div = $(this).closest('.tablerow');
    cancel_invitition = $(main_div).find('input[name=space_user_id]').val();
  });

  $(document).on('click','.cancel_invitation_trigger', function(){
  main_div = $(this).closest('.tablerow');
  var csrf_token = $('meta[name="csrf-token"]').attr('content');
  var settings = {
    "async": true,
    "crossDomain": true,
    "url": baseurl+"/cancel_invitation/"+cancel_invitition,
    "method": "get",
    "headers": {
        "cache-control": "no-cache",
        'X-CSRF-TOKEN': csrf_token
    }
  }
    $.ajax(settings).done(function(response) {
      $('#cancelinvitepopup').modal('hide');
      mixpanelLogger({
        'space_id': session_space_id, 
        'event_tag':'Cancel Pending Invite'
      }, true)
      $('.setting_tabs li a[href="#pending-invites-tab').trigger('click');
    });
  });

  $("input[name=first_name]").on('change, keyup paste input', function() {
    if($(this).val().length){
       $(this).parent().parent().parent().find(".mailbody").find('span').eq(0).show();
    } else {
       $(this).parent().parent().parent().find(".mailbody").find('span').eq(0).hide();
       $(this).parent().parent().parent().find(".mailbody").find('span').eq(1).html('');
    }
    $(this).parent().parent().parent().find(".mailbody").find('span').eq(1).html(' ' + $(this).val() + ',');
  });

   /* domain_inp_edit start */
  $(document).on("click", ".domain_inp_edit", function() {
    var input = $(this).parents('.domain-input-grp').find('.domain_name_inp'); 
    if(!addEditOpenedElementValidation()){
      return false;
    }
    input.attr('disabled', false);
    input.focus();
    var parent_dropdown = $(this).closest('div.dropdown')
    parent_dropdown.children('a.dropdown-toggle').hide();
    parent_dropdown.children('.dropdown-save-btn').show();
    val_temp = $(this).parent().parent().parent().find('.domain_name_inp').val();
    input.val( '' );
    input.val(val_temp)
  });
   /* domain_inp_edit end */

  $(document).on('click', 'a[href="#domain-management-tab"]',function(){
    var domain_selector = $('#domain-management-tab .tab-inner-content .domain-input-grp')
    domain_selector.show();
    domain_selector.find('.domain_name_inp').show();
  }); 

   /* domain_inp_delete start */
  
  $(document).on("click", ".delete-link", function(e) {
    e.preventDefault();
    if($('.form_field_section .domain-action-btn:visible').length > 0){
      addEditOpenedElementValidation();
    }else{
      $('#domain-management-tab').find('.error-msg').remove();
      if($("#domain-management-tab").find('.domain_management_form  .input-group.domain-input-grp').length == 1){
        $('.form_field_section .domain_name_inp').after('<span class="error-msg text-left">' + "There should atleast one domain" + '</span>');
        $('.form_field_section .domain_name_inp').parent().addClass("has-error");
        return false;
      }
      var input_group = $(this).closest('.input-group.domain-input-grp');
      var modal_delete_btn = $('.delete-domain-popup .domain_inp_delete');
      modal_delete_btn.attr('detete-id', input_group.attr('data-id'));
      $('#delete_domain_popup').modal('show');
    }
  })
  
  $(document).on("click", ".domain_inp_delete", function() {
    var input_group = $('.input-group.domain-input-grp[data-id="'+ $(this).attr('detete-id')+'"]');
    var domain=input_group.find('input').val();
    $.ajax({
            type: "POST",
            "headers": {
                "cache-control": "no-cache",
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: baseurl + '/delete-allowed-domain/' + session_space_id,
            data: {domain: domain},
            success: function (response) {
                if (response.success == 1) {
                  input_group.closest('.domain-input-grp').remove();
                } else {
                  input_group.closest('.domain-input-grp').find('.domain_name_inp').after('<span class="error-msg text-left">' +response.message + '</span>');
                  input_group.closest('.domain-input-grp').find('.domain_name_inp').parent().addClass("has-error");
                }
            },
            error: function(error) {
            custom_logger({
              'action': 'Delete allowed domain in settings',
              'description': 'An admin tried to delete allowed domain in settings but was unsuccessful',
              'metadata': JSON.stringify(error)
            });
        }
    });
    
    $('#delete_domain_popup').modal('hide');
  });
  
  $(document).on('click','.cancel-domain-editing',function(){
    var parent_dropdown = $(this).closest('div.dropdown')
    input_value = parent_dropdown.children('a.dropdown-toggle').attr('data-domain')
    $(this).closest('.domain-input-grp').children('input.domain_name_inp').val(input_value);
    $(this).closest('.domain-input-grp').children('input.domain_name_inp').attr('disabled', true)
    console.log(input_value);
    parent_dropdown.children('a.dropdown-toggle').show();
    parent_dropdown.children('.dropdown-save-btn').hide();
    $(this).closest('.domain-input-grp').removeClass("has-error").find('.error-msg').remove();
  });
  
   /* domain_inp_delete end */

   /* Add Domain row start*/
  $('.add_domain_row').on('click', function(){
    domain_skull = $('.add_domain_skull').clone();
    domain_skull.show();
    domain_skull.removeClass('add_domain_skull');
    domain_skull.find('input').addClass('domain_name_inp');

    var ele = $('.form_field_section').find('.domain-input-grp').length-1;

    if( ele < 0) {
       $('.form_field_section').find('.input-group').eq(0).after(domain_skull);
    }else {
      if(!addEditOpenedElementValidation()){
        return false;
      }
      $('.form_field_section').find('.domain-input-grp').eq(ele).after(domain_skull);
    }
    domain_skull.find('.domain_name_inp').focus();
  });

  $(document).on('keypress, keyup', '.domain_name_inp', function(e) {
    $(this).val( $(this).val().toLowerCase() );
    var block_key = [64,32,44,59];
      if (block_key.indexOf(e.which) > -1) {
        return false;
      }
  });

   /**/
  $('.white_box_info').on('click', function() {
    $(this).fadeOut('fast');
  });

   /* Remove error-msg from input when value change */
  $("textarea, input").on('change, keyup paste input', function() {
    $(this).parent().removeClass('has-error');
    $(this).parent().find('.error-msg').remove();
  });

   /* reset form and div */
  $('#myModalInvite').on('show.bs.modal', function () {
    $('.white_box_info').fadeOut('fast');
  });

  $("#myModalInvite").on('hide.bs.modal', function() {
    location.reload();
  });


  if($(".show_tab").attr('showtab').length>0){
    var show_tab = $(".show_tab").attr('showtab');
    $('a[href="#'+show_tab+'"]').parent().addClass('active');
    $('#'+show_tab).show();
    cleanUrl("#"+show_tab);
  }else{
    setTimeout(menu_pre_select, 1000);
  }

  $(".save_notification_email_status").prop('disabled', true);
  $(".save_password").prop('disabled', true);
    if(localStorage.getItem("Status")) {
      $('.box').scrollTop();
       $('.pending_noti_msg').show();
        setTimeout(function(){
               $(".pending_noti_msg").fadeOut();
               localStorage.clear();
      }, 3000);
    }

  $('.post_permission').on('change', function() {
    $(this).closest('form').find('.btn.btn-primary').removeAttr('disabled');
  });

  $(document).on('click','.setting-tab', function(){
    cleanUrl($(this).attr('href'));
  });

  $(".edit_user_name").on('click', function(e) {
    $(this).hide();
    $(".user_first_last_name").hide();
    $(".edit_user").show();
  });

  $(".cancel_user_name").on('click', function(e) {
    $(".user_first_last_name").show();
    $(".edit_user").hide();
    var first_prev_name = $("#first_prev_name").val();
    var last_prev_name = $("#last_prev_name").val();
    $("#first_name").val(first_prev_name);
    $("#last_name").val(last_prev_name);
    $(".first_name_text_error").css("display", "none");
    $(".last_name_text_error").css("display", "none");
    $(".edit_user_name").show();
  });

  $(document).on('keyup', '#first_name', function() {
      var char = $('#first_name').val().length;
      if( char > 0){ 
          $(".first_name_text_error").css("display", "none");
      }
  });

  $(document).on('keyup', '#last_name', function() {
      var char = $('#last_name').val().length;
      if( char > 0){ 
          $(".last_name_text_error").css("display", "none");
      }
  });

  $(document).on('click','.check_hover_dots',function(){    
    if($('.check_hover_dropdown').hasClass('open')){    
       $('.check_hover_dropdown').parent().parent().parent().find('.tablerow-detail').addClass('fix_hover');
       $('.pending-eye').removeClass('active');    
    } else {    
       $('.check_hover_dropdown').parent().parent().parent().find('.tablerow-detail').removeClass('fix_hover');   
    }   
  });

  var mouse_is_outside_eye = false;
  $('.pending-eye').hover(function(){
        mouse_is_outside_eye=true;
  }, function(){
        mouse_is_outside_eye=false;
  });

  $(document ).on('click','body',function(){   
    if($('.check_hover_dropdown').hasClass('open')){    
         $('.check_hover_dropdown').parent().parent().parent().find('.tablerow-detail').removeClass('fix_hover');   
    }
    if(!mouse_is_outside_eye){
      if($('.pending-eye').hasClass('active')){
        $('.pending-eye').removeClass('active');
      }
    }
  });

  $(document).on('click','.pending-eye',function(){    
    var eye_id =  $(this).attr('data-id');
      if($('.pending-history-'+eye_id).hasClass('active')){
          $('.pending-history-'+eye_id).removeClass('active');
      } else {    
       $('.pending-eye').removeClass('active');
        $('.pending-history-'+eye_id).addClass('active');
      }  
  });

  $('#editcompany').on('hidden.bs.modal', function () {
    $('.bootstrap-select').prop('selectedIndex',0);//reset selec box
    $('.setting_tabs a[href="#user-management-tab').trigger('click');
  });

  $(document).on('click','.cancel-edit-box',function(){
    $('.setting_tabs a[href="#user-management-tab').trigger('click');
  });

  $(document).on('click','.current_pass,.new_pass,.new_confirm_pass',function(){
    $(".save_password").prop('disabled', false);
  });
  $(document).on("click", ".save_password", function(e) {
    $(".save_password").prop('disabled', true);
    e.preventDefault();
    var current_password = $('.current_pass').val();
    var new_password = $('.new_pass').val();
    var confirm_password = $('.new_confirm_pass').val();
    var validate_new_password = true;
    var validate_confirm_password = true;
    var over_all_val = true;
    var password_min_length = 8
    var password_max_length = 60
    var error = { "color": "#FF647C","font-size": "11px","letter-spacing": "0","line-height": "12px","margin-top": "8px" };
    $('.current_pass_error').html('');

    if(current_password && new_password && confirm_password){
      if(new_password.length < password_min_length || new_password.length>password_max_length)
         validate_new_password = false;
      if(confirm_password.length < password_min_length || new_password.length>password_max_length)
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
        $('.new_pass_error').css(error).html('Your password must be a minimum of '+password_min_length+' characters and maximum of '+password_max_length+' characters and contain an upper case letter, lower case letter and a number');
            over_all_val = false;
      }else{
        $('.new_pass_error').html('');
      }
      if(!validate_confirm_password){
        $('.new_confirm_pass_error').css(error).html('Your password must be a minimum of '+password_min_length+' characters and maximum of '+password_max_length+' characters and contain an upper case letter, lower case letter and a number');
           over_all_val = false;
      }else{
        $('.new_confirm_pass_error').html('');
      }
      if(new_password != confirm_password){
        $('.new_confirm_pass_error').css(error).html('Password & Confirm Password must be same');
           over_all_val = false;
      }
    }
    if(!current_password){
      $('.current_pass_error').css(error).html('Please enter current password');
      over_all_val = false;
    }
    if(!new_password){
      $('.new_pass_error').css(error).html('Please enter new password');
      over_all_val = false;
    }
    if(!confirm_password){
      $('.new_confirm_pass_error').css(error).html('Please confirm new password again');
      over_all_val = false;
    }
    if(current_password != ''){
      $.ajax({
        type: "GET",
        url: baseurl+'/check_pass?current_pass=' + current_password,
        success: function(response) {
          if(response == 'false'){
            $('.current_pass_error').css(error).html('Please Enter Current Password');
            over_all_val = false;
            e.preventDefault();
          }
          if(response == 'true' && over_all_val==true){
            var  formdata=$( ".change_password_form" ).serialize();
            $.ajax({
              url :  baseurl+'/setting/update_password',
              method:'post',
              data:formdata,
              success : function (response) {
                $('div.changepasswordalert').text(response).show();
                setTimeout(function(){$('.changepasswordalert').fadeOut();}, 2000);
                $('input.setting-pass-update').val('');
              },
              error:function(error){
                console.log(error);
                $('input.setting-pass-update').val('');
              }
            });
          }
        },
      error: function(message) {
        alert(message);
      }
    });
  }else{
    return false;
  }
  });

  var path = window.location.href;
  var setting_url = path.split("#");
  if(setting_url[1] == 'bulk-add-users'){
    $('#bulk_add_users').css('display','block');
  }
});
  $(document).on('click','#bulk-invite-export',function(){
    $('.mail-content,.bulk-invite-mail').hide();
    $('.bulk-invite-export').show();    
  });
  $(document).on('click','#bulk-invite-mail',function(){
    $('.mail-content,.bulk-invite-mail').show();
     $('.bulk-invite-export').hide();
  });  


  function func_edit_company(element){
    $('.more-options-wrap').show()
    $('.more-options-wrap').next('.save-user-company').hide()
    $(element).closest('.more-options-wrap').hide();
    $(element).closest('.more-options-wrap').next('.save-user-company').show();
    var userid = $(element).attr('userid');
    $('.companyName').show();
    $('.companyNameEdit').hide();
    $('.user'+userid).find('.companyName').hide();
    $('.user'+userid).find('.companyNameEdit').show();
    $('.user'+userid).find('.bootstrap-select').prop('selectedIndex',0);//reset selec box
  }

  function change_company(element){
    var userId = $(element).attr('user-id');
    var companyId = $(element).val();
    $('.modal_title').html( "Change "+$(element).closest('.tablerow').find('.mem_name').html().trim()+"'s Company " );
    $('.modal_text').html( "Are you sure you want to change "+$(element).closest('.tablerow').find('.mem_name').html().trim()+"'s selected Company? " );
    $('.hidden_company_id').val(companyId);
    $('.hidden_user_id').val(userId);
  }
  
  function update_user_company(){
    var company_id = $('.hidden_company_id').val();
    var user_id = $('.hidden_user_id').val();
    if(company_id == ""){
      $('.setting_tabs a[href="#user-management-tab').trigger('click');
    }
    $.ajax({
      type: "GET",
      beforeSend: function() {
        $('.main_content_loader').show();
      },
      url:  baseurl+'/companyupdate?company_id='+company_id+ '&user_id=' + user_id+'&space_id='+current_space_id ,
      success: function(response) {
      $('.setting_tabs a[href="#user-management-tab').trigger('click');
      $('#editcompany').modal('hide');
      $('.user'+user_id).find('.companyNameEdit').hide();
      $('.user'+user_id).find('.companyName').show();
      $('.user'+user_id).find('.companyName').html($('.user'+user_id).find(".select_company_n option[value='"+company_id+"']").text());
      },error: function(message) {  },
      complete: function() {
        $('.main_content_loader').hide();
        $('.more-options-wrap').show()
        $('.more-options-wrap').next('.save-user-company').hide()
      },
    });
    
  }

  $(document).on('click', '.setting_tabs a[href="#password-tab"]', function() {
    $('.setting-pass-update').val('');
  })
  
  $(document).on('keyup', '.setting-pass-update', function() {
    $('.pass-error').text('');
  })
  
  $(document).on('click', '.cancel-domain-adding', function() {
    $(this).closest('.domain-input-grp').remove()
  })
  
$(document).on('click', '#submit_permissions', function (e) {
    e.preventDefault();
    var formdata = $("#user_permissions").serialize();
    $.ajax({
        type: 'POST',
        url: baseurl + "/user-allow-posting",
        data: formdata,
        success: function (response) {
           $('.permission-settings-alert').text(response.message.success).show().delay(2500).fadeOut();
            return false;
        },
        error: function (error)
        {
            $('.permission-settings-alert').text('Something went wrong !!').show().delay(2500).fadeOut();
        }
    })
});
 