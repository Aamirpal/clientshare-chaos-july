$(document).ready(function(){
    $('.mi-overlay-div').hide();
    $(document).on('click','#user-search-button',function(){
        $('.error-message').text('');
        var expression = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        var first_name = $('.user_first_name').val().trim();
        var last_name = $('.user_last_name').val().trim();
        var email = $('.user_email').val().trim();
        var message = false;
        if(first_name == '' && last_name == '' && email == ''){
            $('.search-error').text("Please fill the field's.");
            message = true; 
        }else if(first_name == '' && last_name != ''){
            $('.first-name-error').text("Please fill first name.");
            message = true; 
        }else if(first_name != '' && last_name == '' ){
            $('.last-name-error').text("Please fill last name.");
            message = true; 
        }
        if(email != '' && !expression.test(email)){
            $('.email-error').text("Please enter correct email address.");
            message = true; 
        }
        if(message){
            return false;
        }
        $('#user-share-search').submit();
    });

    $(document).on('submit','#user-share-search',function(){
        var user_search_button = $('#user-search-button');
        user_search_button.attr('disabled', true);
        var form_data = new FormData($('#user-share-search')[0]); 
        var csrf_token = $('meta[name="csrf-token"]').attr('content');
        $(".user-data-grid").find("tr.row-data").remove();
        $.ajax({
          type: "POST",
          headers: {
              "cache-control": "no-cache",
              'X-CSRF-TOKEN': csrf_token,
          },
          url: baseurl+'/space-search-by-user',
          data: form_data,
          async: false,
          beforeSend: function(){
            hideLoader();
          },
          success: function (response) {
          user_search_button.attr('disabled', false);
          $('.mi-overlay-div').hide();
           if(response.length){ 
              renderView(response);
           }else{
              $('.no-row-result').text('No result found');
           }
          },
          error: function(response){
            alert('Something went wrong. Try again later.');
          },
          cache: false,
          contentType: false,
          processData: false
        });
        return false;
    });
});

function renderView(view_data){
  var source = $("#user-share-record").html();
  var template = Handlebars.compile(source);
  var html='';
  if(view_data){
    $.each(view_data, function(index, space){ 
        var metadata = JSON.parse(space.data.metadata);
        html += template({
          'data':space.data,
          'invitation_code':metadata.invitation_code,
          'user_type': constants.USER_TYPE
        });
    });
  }
  $('.user-data-grid').append(html);
}

function hideLoader(){
  $('.mi-overlay-div').show();
}

$(document).on('click', '.populate_user_details', function() {
    var modal = $('#promote_user_by_admin');
    var row = $('tr.'+$(this).attr('data-uid'));

    modal.find('.space_id').val($(this).attr('data-space-id'));
    modal.find('.user_id').val($(this).attr('data-user-id'));
    modal.find('.uid').val($(this).attr('data-uid'));
    
    modal.find('span.share_name').html(row.find('.share_name').html());
    modal.find('span.username').html(row.find('.first_name').html()+' '+row.find('.last_name').html());
});

$(document).on('click', '.promote_user', function() {
  var modal = $('#promote_user_by_admin');
  var userid = modal.find('.user_id').val();
  var spaceid = modal.find('.space_id').val();
  var row = $('tr.'+modal.find('.uid').val());
  
  $.ajax({
    type: "GET",
    url:  baseurl+'/promote_admin?spaceid='+spaceid+ '&userid=' + userid,
    beforeSend: function(){
      modal.find('.form-submit-loader').show();
    },
    success: function (response) {
      row.find('.user_type').html('Admin');
      row.find('.dropdown-menu .populate_user_details').hide();
    },
    complete: function(){
      modal.modal('hide');
      modal.find('.form-submit-loader').hide();
    }
  });
});


$(document).on('click', 'a.remove_user', function() {
  var modal = $('#removeuserpopup');  
  modal.find('.space_id').val($(this).attr('data-space-id'));
  modal.find('.user_id').val($(this).attr('data-user-id'));
  modal.find('.uid').val($(this).attr('data-uid'));

  var row = $('tr.'+modal.find('.uid').val());
  modal.find('.share_name').html(row.find('.share_name').html());
  modal.find('.username').html(row.find('.first_name').html()+' '+row.find('.last_name').html());
});

$(document).on('click', 'button.remove_user', function() {
  var modal = $('#removeuserpopup');
  var user_id = modal.find('.user_id').val();
  var space_id = modal.find('.space_id').val();
  var row = $('tr.'+modal.find('.uid').val());

  $.ajax({
    type: "GET",
    url:  baseurl+'/inactive_user?id='+user_id+ '&space_id=' + space_id ,
    beforeSend: function(){
      modal.find('.form-submit-loader').show();
    },
    success: function (response) {
      text = row.find('.user_share_status').html().trim() == 'Pending' ? 'Cancelled' : 'Deleted';
      row.find('.user_share_status').html(text);
      row.find('.dropdown-menu .remove_user').hide();
    },
    complete: function(){
      modal.modal('hide');
      modal.find('.form-submit-loader').hide();
    }
  });
});