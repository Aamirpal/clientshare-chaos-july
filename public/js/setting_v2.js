
$(document).on('keyup', 'input', function(){
  $(this).parent().find('span.error-msg').remove();
});

$(document).on('click', '.remove_report', function(){
  $('#removeReport').modal('show');
  $('#removeReport').find('input.report_id_input').val($(this).attr('data-report-id'));
});

function removeReportTrigger(element){
  report_id = $(element).closest('.modal').find('input.report_id_input').val();
  $(element).closest('.modal').modal('hide');
  removeReport(report_id);
}

function removeReport(report_id){
  $.ajax({
    type: 'GET',
    url: baseurl+"/remove_report/"+session_space_id+"/"+report_id,
    beforeSend: function(){
      $('.main_content_loader').show();
    }, 
    success: function (response) {
      $('.setting_tabs li a[href="#power-bi-tab"]').trigger('click');
    },
    complete: function(){
      $('.main_content_loader').hide();
    }
  })
}

$(document).on('change', '.power-bi-reports-type', function(e){
  e.stopImmediatePropagation();
  report = {  
    'report': ['Function URL', 'Report ID'],
    'dashboard': ['Function URL', 'Dashboard ID']
  };

  report_type = report[$(this).val()];
  html='';
  $.each(report_type, function(index){
    html += '<div class="form-group" ><label for="'+report_type[index]+'">'+report_type[index]+' </label><input class="form-control '+report_type[index]+'" name="report_credentials['+report_type[index]+']"></div>';
  });

  $('.bi-credentials').html(html);
  $('.bi-credentials').closest('form').find('.common-report-columns').removeClass('hidden');
  $('.bi-credentials').closest('.modal').find('.modal-footer').removeClass('hidden');
});

$(document).ready(function(){
  checkProgressBarsection();
	$(document).on('click', '.setting_tabs a[href="#user-management-tab"]',function(){
		 var space_id = $('.hidden_space_id').val().trim();
         $.ajax({
             type: 'get',
             url: user_management+"?space_id="+space_id,
             beforeSend: function(){
                $('.main_content_loader').show();
             }, 
             success: function (response) {
              $('#user-management-tab').html(response);
              $('.selectpicker').selectpicker('refresh');
              checkProgressBarsection();
                return false;
             },
             error   : function ( response )
             {
               alert('Something went wrong.');
             },
             complete: function(){
              $('.main_content_loader').hide();
             }
          })
	});

	$(document).on("click","#user-management-tab ul.pagination li a",function(e) {
        e.stopPropagation();
        e.preventDefault();
        var space_id = $('.hidden_space_id').val().trim();
        var url=$(this).attr("href");
        $.ajax({
              type: 'get',
              url: url+"&space_id="+space_id, 
              beforeSend: function(){
                $('.main_content_loader').show();
              },     
              success: function(response){
                $('#user-management-tab').html(response);
                $('.selectpicker').selectpicker('refresh');
                return false;
              },
              error: function(response){
                alert('Something went wrong.');
              },
              complete: function(){
                $('.main_content_loader').hide();
              }
         });
      });

    $(document).on('click','.setting_tabs a[href="#pending-invites-tab"]',function(){
		 var space_id = $('.hidden_space_id').val().trim();
         $.ajax({
             type: 'get',
             url: pending_invites+"?space_id="+space_id,
             beforeSend: function(){
                $('.main_content_loader').show();
             }, 
             success: function (response) {
              $('#pending-invites-tab').html(response);
              $('.selectpicker').selectpicker('refresh');
                return false;
             },
             error   : function ( response )
             {
               alert('Something went wrong.');
             },
             complete: function(){
              $('.main_content_loader').hide();
             }
          })
	});

	$(document).on("click","#pending-invites-tab ul.pagination li a",function(e) {
        e.stopPropagation();
        e.preventDefault();
        var space_id = $('.hidden_space_id').val().trim();
        var url=$(this).attr("href");
        $.ajax({
              type: 'get',
              url: url+"&space_id="+space_id,     
              beforeSend: function(){
                $('.main_content_loader').show();
              },  
              success: function(response){
                $('#pending-invites-tab').html(response);
                $('.selectpicker').selectpicker('refresh');
                return false;
              },
              error: function(response){
                alert('Something went wrong.');
              },
              complete: function(){
                $('.main_content_loader').hide();
              }
         });
      });

});


$(document).on('click','.setting_tabs li a[href="#power-bi-tab"]',function() {
    $.ajax({
      type: 'GET',
      url: baseurl+'/get_report_list/'+session_space_id,
      beforeSend: function(){
        $('.main_content_loader').show();
      },  
      success: function(response){
        var source = $("#power_bi_reports_list").html();
        var template = Handlebars.compile(source);
        var html='';

        $.each(response, function(index, report){
          report['index'] = index+1;
          html += template(report);
        });
        $('.report_block').html(html);
      },
      error: function(response){
        alert('Something went wrong.');
      },
      complete: function(){
        $('.main_content_loader').hide();
      }
   });
});

function reportValdationError(errorLog) {
  $.each(errorLog.errors, function(key){
    error_key = key.split('.');
    element = $('form.power-bi-reports-form').find('input[name="report_credentials['+error_key[1]+']"]');

    if(!element.length){
      element = $('form.power-bi-reports-form [name="'+key+'"]');
    }

    element.parent().find('span.ip-error-msg').remove();
    element.after('<span class="left ip-error-msg error-msg">'+errorLog.errors[key]+'</span>');
  });
}

function createReport() {
  var form_data = $('form.power-bi-reports-form').serializeArray();
  form_data.push({'name': 'space_id', 'value': session_space_id});
  $.ajax({
    type: 'POST',
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    url: baseurl+'/create_report',
    data: form_data,
    beforeSend: function(){

    },
    success: function (response) {
      $('form.power-bi-reports-form').closest('.modal').modal('hide')
      resetReportModal()
      $('.setting_tabs li a[href="#power-bi-tab"]').trigger('click');
    },
    error: function(xhr, status, error) {
      if(xhr.status){
        reportValdationError(xhr.responseJSON);
      }
    }
  });
}

$('#power-bi-modal').on('hidden.bs.modal', function (e) {
  resetReportModal()
})

function resetReportModal(){
  var form = $('form.power-bi-reports-form');
  $('form.power-bi-reports-form').trigger("reset");
  form.find('.bi-credentials').html('');
  form.find('.common-report-columns').addClass('hidden');
  form.closest(".modal").find(".modal-footer").addClass('hidden');
  $('.ip-error-msg.error-msg').remove()
  $('.selectpicker').selectpicker('refresh')
}

function checkProgressBarsection(){
    $.ajax({
        type: 'GET',
        url: baseurl+'/get_share_profile_status?space_id='+session_space_id,
        beforeSend: function(){
            $('#user-management-tab .invite-btn, #user-management-tab .lastrow .tablecell').hide();
        }, 
        success: function (response) {
            if(response.data.progress == parseInt(100) || response.data.space_users) {
                $('#user-management-tab .invite-btn, #user-management-tab .lastrow .tablecell').show();
                return false;
            }
            if(response.result){
                $('#user-management-tab .invite-btn, #user-management-tab .lastrow .tablecell').remove();
            }
        },
        error: function(xhr, status, error) { errorOnPage(xhr, status, error); }
    });
}

$(document).ready(function(){
  checkProgressBarsection();
	$(document).on('click', '.setting_tabs li a[href="#user-management-tab"]',function(){
    var space_id = $('.hidden_space_id').val().trim();
    $.ajax({
      type: 'get',
      url: user_management+"?space_id="+space_id,
      beforeSend: function(){
        $('.main_content_loader').show();
      }, 
      success: function (response) {
      $('#user-management-tab').html(response);
      $('.selectpicker').selectpicker('refresh');
      checkProgressBarsection();
        return false;
      },
      error   : function ( response )
      {
        alert('Something went wrong.');
      },
      complete: function(){
        $('.main_content_loader').hide();
      }
    })
  })



  $(document).on('click','.setting_tabs li a[href="#pending-invites-tab"]',function(){
    var space_id = $('.hidden_space_id').val().trim();
    $.ajax({
      type: 'get',
      url: pending_invites+"?space_id="+space_id,
      beforeSend: function(){
        $('.main_content_loader').show();
      }, 
      success: function (response) {
      $('#pending-invites-tab').html(response);
      $('.selectpicker').selectpicker('refresh');
        return false;
      },
      error   : function ( response )
      {
        alert('Something went wrong.');
      },
      complete: function(){
        $('.main_content_loader').hide();
      }
    })
  });


    $(document).find('.setting_tabs li a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if ($(window).width() < 768) {
            $('.settings-tabs-wrap').hide();
            $(".settings-content-wrap").show();
        }
    });

    $('.settings-mobile-back-btn').on('click', function () {
      $('.settings-tabs-wrap').show();
      $(".settings-content-wrap").hide();
      $(".setting_tabs li a.active").removeClass("active");
  });

  var url = location.pathname;
  $(window).on("load", function () {
    if ($(window).width() < 768) {
      $(".settings-content-wrap").hide();
      $('.settings-tabs-wrap').show();
      $(".setting_tabs li a").removeClass("active");
    }
    else {
      $(".settings-content-wrap").show();
    }
  });
  if ($(window).width() < 768 && (url.indexOf('setting') > -1)) {
    $("body").addClass("v2-settings-mobile");
    $('.clientshare-v2-container').addClass('settings-mobile-container'); 
  }
})
