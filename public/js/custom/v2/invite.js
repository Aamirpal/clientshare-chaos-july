function send_mail(btn) {
  $('.white_box_info').hide();
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

  var subject = $('.subjectbody').find('span:visible').html();
  var settings = {
    "async": true,
    "crossDomain": true,
    "url": baseurl + "/invite_user",
    "method": "post",
    "headers": {
      "cache-control": "no-cache",
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    "data": {
      "share_id": current_space_id,
      "user_type": $("input[name=user_type]").val(),
      "user": {
        "first_name": $("input[name=first_name]").val(),
        "last_name": $("input[name=last_name]").val(),
        "email": $("input[name=email]").val(),
        "subject": subject
      },
      "mail": {
        "to": $("input[name=email]").val(),
        "body": mail
      }
    }
  }
  $.ajax(settings).done(function(response) {
    if (response.code == 0) {
      $('.white_box_info').addClass('success-msg');
      $('.white_box_info').removeClass('error-message warning-message');
      $('.white_box_info').html("Invitation sent successfully");
      $('.white_box_info').show();
      //$(".white-popup").find("input[type=text],span, textarea").val("");
      $(".white-popup").find("input[type=text]").val("");
      $('.mailbody').find("span").eq(0).hide();
      $('.mailbody').find("span").eq(1).html("");
    } else if (response.code == 400) {
      $('.white_box_info').removeClass('success-msg');
      $('.white_box_info').addClass('warning-message');
      $('.white_box_info').html(response.message);
      $('.white_box_info').show();
    } else {
      if (response.message['user.first_name']) {
        $("input[name=first_name]").parent().addClass('has-error');
        $("input[name=first_name]").parent().find('.error-msg').remove();
        $("input[name=first_name]").after('<span class="error-msg text-left">' + response.message['user.first_name'] + '</span>');
      } else {
        $("input[name=first_name]").parent().removeClass('has-error');
        $("input[name=first_name]").parent().find('.error-msg').remove();
      }
      if (response.message['user.last_name']) {
        $("input[name=last_name]").parent().addClass('has-error');
        $("input[name=last_name]").parent().find('.error-msg').remove();
        $("input[name=last_name]").after('<span class="error-msg text-left">' + response.message['user.last_name'] + '</span>');
      } else {
        $("input[name=last_name]").parent().removeClass('has-error');
        $("input[name=last_name]").parent().find('.error-msg').remove();
      }
      if (response.message['user.email']) {
        $("input[name=email]").parent().addClass('has-error');
        $("input[name=email]").parent().find('.error-msg').remove();
        $("input[name=email]").after('<span class="error-msg text-left">' + response.message['user.email'] + '</span>');
      } else {
        $("input[name=email]").parent().removeClass('has-error');
        $("input[name=email]").parent().find('.error-msg').remove();
      }
    }
    $(btn).attr('disabled', false);
    $(btn).html('Invite');
  }).error(function() {
    $('.white_box_info').removeClass('success-msg');
    $('.white_box_info').addClass('error-message');
    $('.white_box_info').html("Something went wrong, Try again later!!");
    $('.white_box_info').show();
    $(btn).attr('disabled', false);
  });
}

function set_email_rule() {
  var settings = {
    "async": true,
    "crossDomain": true,
    "url": baseurl+"/clientshare/"+current_space_id,
    "method": "put",
    "headers": {
      "cache-control": "no-cache",
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    "data": {
      "metadata": {
        "rule": $(".set_email_rule").serializeArray()
      },
      "_method": "put",
    }
  }
  $.ajax(settings).done(function(response) {
    input = $(".set_email_rule").find('input');
    input.parent().find('.error-msg').remove();
    input.parent().removeClass("has-error");
    if (response.code) {
      $.each(response.message, function(index, value) {
        index = index.split(".");
        input = $(".set_email_rule").find('input').eq(index[0]);
        input.parent().find('.error-msg').remove();
        input.parent().removeClass("has-error");
        input.attr('disabled', false);
        input.focus();
        input.after('<span class="error-msg text-left">' + value + '</span>');
        input.parent().addClass("has-error");
      });
    } else {
      $('.blue-popup').fadeOut('fast');
      $('.domain_name_inp').each(function() {
        html = $('.white-popup').find('.white_box_info').html();
        $('.white-popup').find('.white_box_info').html(html + ", " + $(this).val());
      });
      $('.white_box_info').show();
      $(".white-popup").removeClass('disable');
    }
  });
}

$(document).on('click', '.radio-invite', function() {
  if ($('#radio-invite-url').is(':checked')) {
    $('.invite-email, .btn-invite').hide();
    $('.create_url').show();
  } else {
    $('.invite-email, .btn-invite, .invite-cancel-btn').show();
    $('.create_url,.short_url,.btn-done').hide();
  }
});
$(document).on('click', '.create_url', function() {
  var first_name = $("input[name=first_name]").val();
  var last_name = $("input[name=last_name]").val();
  var email = $("input[name=email]").val();
  var user_type = $("input[name=user_type]").val();
  var settings = {
    "async": true,
    "crossDomain": true,
    "url": baseurl+"/createlink",
    "method": "get",
    "headers": {
      "cache-control": "no-cache",
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    "data": {
      share_id: current_space_id,
      first_name: first_name,
      last_name: last_name,
      email: email,
      user_type: user_type
    },
  }
  $.ajax(settings).done(function(response) {
    if (response.success == true) {
      $('.short_url,.btn-done').show();
      $('.create_url').hide();
        if ($(window).width() < 768) {
             $('.btn-done, .btn-invite, .invite-cancel-btn').hide();
         }
      $('#invite_url').val(response.url).select();
      $("input[name=email],input[name=first_name],input[name=last_name]").prop("readonly", true);
      $('.white_box_info').hide();
    } else if (response.code == 400) {
      $('.white_box_info').removeClass('success-msg');
      $('.white_box_info').addClass('warning-message');
      $('.white_box_info').html(response.message);
      $('.white_box_info').show();
    } else {
      if (response.message['first_name']) {
        $("input[name=first_name]").parent().addClass('has-error');
        $("input[name=first_name]").parent().find('.error-msg').remove();
        $("input[name=first_name]").after('<span class="error-msg text-left">' + response.message['first_name'] + '</span>');
      } else {
        $("input[name=first_name]").parent().removeClass('has-error');
        $("input[name=first_name]").parent().find('.error-msg').remove();
      }
      if (response.message['last_name']) {
        $("input[name=last_name]").parent().addClass('has-error');
        $("input[name=last_name]").parent().find('.error-msg').remove();
        $("input[name=last_name]").after('<span class="error-msg text-left">' + response.message['last_name'] + '</span>');
      } else {
        $("input[name=last_name]").parent().removeClass('has-error');
        $("input[name=last_name]").parent().find('.error-msg').remove();
      }
      if (response.message['email']) {
        $("input[name=email]").parent().addClass('has-error');
        $("input[name=email]").parent().find('.error-msg').remove();
        $("input[name=email]").after('<span class="error-msg text-left">' + response.message['email'] + '</span>');
      } else {
        $("input[name=email]").parent().removeClass('has-error');
        $("input[name=email]").parent().find('.error-msg').remove();
      }
    }

  });
});
$(document).on('click', '.btn-done', function() {
  $('input[name=first_name],input[name=last_name],input[name=email],#invite_url').val('');
  $("input[name=email],input[name=first_name],input[name=last_name]").prop("readonly", false);
  $('.short_url,.btn-done').hide();
  $('.create_url').show();
});
$(document).on('click', '.invite-cancel-btn', function() {
  $('input[name=first_name],input[name=last_name],input[name=email],#invite_url').val('');
  $("input[name=email],input[name=first_name],input[name=last_name]").prop("readonly", false);
  $(".for-user-invite, #radio-invite-mail").trigger('click');
  $('.invite-email, .btn-invite, .invite-cancel-btn').show();
  $('.create_url,.short_url,.btn-done').hide();
});
$(document).on('click', '.copy-link', function() {
  copyToClipboard(document.getElementById("invite_url"));
  $('#invite_url').select();
    swal({
        title: "Link copied!",
        timer: 2000
    });
    $('#myModalInvite').modal('toggle');
    $(".btn-done").show().trigger('click');
});
$(document).on('click', '.close', function() {
  $('.error-msg').hide();
});

function copyToClipboard(elem) {
  var targetId = "_hiddenCopyText_";
  var is_input = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
  var orig_selection_tart, orig_selection_end;
  if (is_input) {
    target = elem;
    orig_selection_tart = elem.selectionStart;
    orig_selection_end = elem.selectionEnd;
  } else {
    target = document.getElementById(targetId);
    if (!target) {
      var target = document.createElement("textarea");
      target.style.position = "absolute";
      target.style.left = "-9999px";
      target.style.top = "0";
      target.id = targetId;
      document.body.appendChild(target);
    }
    target.textContent = elem.textContent;
  }
  var current_focus = document.activeElement;
  target.focus();
  target.setSelectionRange(0, target.value.length);
  var succeed;
  try {
    succeed = document.execCommand("copy");
  } catch (e) {
    succeed = false;
  }
  if (current_focus && typeof current_focus.focus === "function") {
    current_focus.focus();
  }

  if (is_input) {
    elem.setSelectionRange(orig_selection_tart, orig_selection_end);
  } else {
    target.textContent = "";
  }
  return succeed;
}

$(document).on('click', '.user-invite-type', function(){
  $(this).parent().find('.user-invite-type').removeClass('active');
  $(this).addClass('active');
  $('.invite-colleague .error-msg').remove();
  $('.modal-header .success-msg').hide();
  if($(this).hasClass('for-admin-invite')) {
    $("input[name=user_type]").val('admin');
    $('.for-admin').show();
    $('.for-user').hide();
    $('textarea.for-admin').css('height', '62px');
  } else {
    $("input[name=user_type]").val('user');
    $('.for-admin').hide();
    $('.for-user').show();
  }
});