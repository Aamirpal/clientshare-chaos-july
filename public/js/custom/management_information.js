var offset = count = 0;
var sort_count = 0;
var record_exist= false;
var date_pick;
var sort = sort_temp = 'supplier';
var sort_order = 'asc';
var suppliers = new Array();
var buyers = new Array();
var shares = new Array();
var RAG_filter = new Array();
var status_filter = include_column_filter = new Array();
var table_offset = $('.navbar').height();
var email_space_info;
var email_modal = $('#mi_email_modal');
var show_confirmation = false;
var finished = 1;
var negative_email = 2;
var share_not_launched = 3;
function GetIEVersion() {
  var agent = window.navigator.userAgent;
  var index = agent.indexOf("MSIE");

  if (index > 0) 
    return parseInt(agent.substring(index+ 5, agent.indexOf(".", index)));

  else if (!!navigator.userAgent.match(/Trident\/7\./)) 
    return 11;

  else
    return 0;
}

function enableMultiSelect(element, placeholder, callback_function, select_all, data_set){
  element.multiselect({
    numberDisplayed: 1,
    includeSelectAllOption: select_all,
    selectAllText: 'Select All',
    enableCaseInsensitiveFiltering: true,
    buttonWidth: '100%',
    nonSelectedText: placeholder,
    onChange: function(option, checked, select) {
      return window[callback_function](option, checked, select, data_set);
    },
    onSelectAll: function(){
      window[data_set] = element.val();
      resetMiAjax();
    },
    onDeselectAll: function(){
      window[data_set] = new Array();
      resetMiAjax();
    }
  });
}


function enableMultiSelectRefined(params){
  params['element'].multiselect({
    numberDisplayed: 1,
    includeSelectAllOption: true,
    selectAllText: 'Select All',
    enableCaseInsensitiveFiltering: true,
    buttonWidth: '100%',
    nonSelectedText: params['placeholder'],
    onChange: function(option, checked, select) {
      window[params['callback_function']](option, checked, select);
    },
    onSelectAll: function(){
      window[params['on_select_all']]();
    },
    onDeselectAll: function(){
      window[params['on_deselect_all']]();
    }
  });
}

function updateRecord(option, checked, select, data_set) {
  if(checked) window[data_set].push(option.val());
  else window[data_set].splice( window[data_set].indexOf(option.val()), 1);
  resetMiAjax();
}

function minimizeDefaultColumn() {
  $('.management-information-table thead.header th .default_hide').each(function() {
    var value = $(this).parent().data('uiclass');
    $('*[data-uiclass="'+ value +'"]').hide();
    $('.mi-columns-multiselect > option[value="'+value+'"]').removeAttr('selected');
  });
  $('.mi-columns-multiselect').multiselect('refresh');
}

function updateColumnVisiblityDropdown() {
  $('.mi-columns-multiselect > option').each(function(ind){
    if($('*[data-uiclass="'+ $(this).val() +'"]:visible').length){
     $(this).attr('selected', true);
    } else {
      $(this).removeAttr('selected');
    }
  });
  $('.mi-columns-multiselect').multiselect('refresh');
}

function hideLoader(){
  $('.mi-overlay-div').removeClass('hidden');
}

function resetColumnVisiblity(option, checked, select){
   $('*[data-uiclass="'+ option.val() +'"]').toggle();
   hideLoader();
   setTimeout(resetTableLayout, 500);
}

function selectAllColumns(){
  $('.management-information-table th:hidden, .management-information-table td:hidden').not('.not-visible').show();
  hideLoader();
  setTimeout(resetTableLayout, 500);
}
function deselectAllColumns(){
  $('.remove-column').trigger('click');
}

$(document).ready(function(){
  enableMultiSelect($('.mi-supplier-multiselect'), 'Supplier', 'updateRecord', true, 'suppliers');
  enableMultiSelect($('.mi-buyer-multiselect'), 'Buyer', 'updateRecord', true, 'buyers');
  enableMultiSelect($('.mi-share-multiselect'), 'Share', 'updateRecord', true, 'shares');
  enableMultiSelect($('.mi-rag-multiselect'), 'Comms', 'updateRecord', true, 'RAG_filter');
  enableMultiSelect($('.mi-status-multiselect'), 'Status', 'updateRecord', true, 'status_filter');
  enableMultiSelectRefined({
    element: $('.mi-columns-multiselect'),
    placeholder: 'Include',
    callback_function: 'resetColumnVisiblity',
    select_all: true,
    data_set: 'include_column_filter',
    on_select_all: 'selectAllColumns',
    on_deselect_all: 'deselectAllColumns'
  });

  updateColumnVisiblityDropdown();
  minimizeDefaultColumn();
   $('.management-date').daterangepicker({
      singleDatePicker: true,
      maxDate: new Date(),
      locale: {
        format: 'dddd Do MMMM YYYY'
      }
   });

   $(document).on('keypress paste','.management-date', function (e) {
      e.preventDefault();
      return false;
   });

   $('.management-date-hidden').daterangepicker({
      singleDatePicker: true,
      maxDate: new Date(),
      locale: {
        format: 'MMM-DD-Y'
      }
   });

   status_filter = $('.mi-status-multiselect').val();

    $('.management-date').on('apply.daterangepicker', function(ev, picker) { 
          var picker = $(ev.target).data('daterangepicker');
          $(this).removeClass('hidden');
          $('.management-date').text(picker.startDate.format('dddd Do MMMM YYYY'));
          $('.management-date-hidden').val(picker.startDate.format('MMM-DD-Y'));
          date_pick = picker.startDate.format('MMM-DD-Y');
          resetMiAjax();
    });
    $('.management-date').trigger("apply.daterangepicker");
    $('.supplier-load').hide();
    $('.supplier-dropdown, .rag-dropdown').removeClass('hidden');

    date_pick = $('.management-date-hidden').val();
    miGetShares(offset,'reset');
    
    $(window).bind('scroll', function() {
        var height = ($( window ).width() < 768)?$(document).height()-Number(220):$(document).height()-Number(150); 
        $('.mi-columns-multiselect').click();
        if($(window).scrollTop() + $(window).height() > height*3/4) {
          if (finished == 1) {
            finished = 0;
            miGetShares(offset,'lazyload');
          }
        }
    });

    $(window).scroll(function() {    
       var scroll = $(window).scrollTop();
       if(GetIEVersion() == 0){
         if (scroll <= 60) {
           $(".tableFloatingHeader").css("display","none");
           $(".tableFloatingHeaderOriginal").removeClass("fixed-header-table");
         }
       }else{
         if (scroll <= 80) {
           $(".tableFloatingHeader").css("display","none");
           $(".tableFloatingHeaderOriginal").removeClass("fixed-header-table");
         }
       }
    });

    $('.download-excel').on('click', function (e) { 
        var include_column_filter = $('select[name="column[]"]').val();
        e.preventDefault();
        var data = {
            'sort' : sort,
            'offset' : 0,
            'date_value': date_pick,
            'sort_order' : sort_order,
            'suppliers': suppliers.length ? suppliers:[''],
            'buyers': buyers.length ? buyers:[''],
            'RAG_filter': RAG_filter.length ? RAG_filter:[''],
            'status_filter' : status_filter.length ? status_filter:[''],
            'include_column_filter' : include_column_filter.length ? include_column_filter:[''],
            'spaces_filter': shares.length ? shares:['']
        };
        $.ajax({
          type: 'POST',
          dataType: 'HTML',
          url: baseurl+'/mi/download/excel',
          data : data,
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          beforeSend: function(){
            var download_excel = $('.download-excel');
            download_excel.css('pointer-events', 'none');
            download_excel.find('a').addClass('disabled');
            download_excel.find('a i').removeClass('hidden');
          },
          success: function() {
            var download_excel = $('.download-excel');
            download_excel.css('pointer-events', '');
            download_excel.find('a').removeClass('disabled');
            download_excel.find('a i').addClass('hidden');
            swal({
              title: 'Download xlsx',
              text: 'A link to download Management Information data in xlsx format has been emailed to you and will be available shortly.',
              confirmButtonText: 'close',
              customClass: 'simple-alert'
            });
          },
          error: function (message) {
            swal({
              text: 'Something went wrong. Try again later.',
              confirmButtonText: 'close',
              customClass: 'simple-alert'
            });
          }
        });
    });

    $(document).on('click',
    '.pending_invites,.community,.post-list,.csi,.nps,.post-interactions, .contract-value, .contract-date, .space-status, .progress_bar',
    function(){
          $("html, body").animate({ scrollTop: 0 }, "slow");
          sort = $(this).attr('sort');
          if(sort_temp != sort)
             sort_count = 1;

          if(sort_temp == sort)
             sort_count = sort_count+parseInt(1);

          sort_order = $(this).attr('sort_order');
          sort_temp = $(this).attr('sort');
          if(sort_count === 3){
              sort = 'supplier';
              sort_order = 'asc';
          }

          var reference = $(this);
          changeOrder(reference,sort_order,sort_count);
    });

    $("html:not(.legacy) table.table-header").stickyTableHeaders({fixedOffset: table_offset});

    $(document).on('click','.communication-table .preview-mi-email', function(){
        var comm_type = $(this).closest('table.communication-table').find('select[name="communication_type"] option:selected').val();
        var space_id = $(this).closest('tr.space-row').attr('data-space');
        setEmailSpaceInfo(space_id);
        var modal_footer = email_modal.find('.modal-footer');
        $(modal_footer).find('input[name="space_id"]').val(space_id);
        $(modal_footer).find('select[name="communication_type"]').selectpicker('val', comm_type);
        setEmailData(comm_type, space_id, true);
        email_modal.modal('show');
    });

    email_modal.on('change','.modal-footer select[name="communication_type"]', function(){
        var comm_type = $(this).find('option:selected').val();
        var space_id = $(this).closest('.modal-footer').find('input[name="space_id"]').val();
        $('#row-'+ space_id).find('.communication-table select[name="communication_type"]').selectpicker('val', comm_type);
        setEmailData(comm_type, space_id, false);
    });

    email_modal.on('hidden.bs.modal', function () {
        var space_id = $('#mi_email_modal input[name="space_id"]').val();
        $('select.communication-type').selectpicker('val','community');
        $('form#edit_email input').val('');
        $('.email-cc, .email-bcc').addClass('hidden');
        if(show_confirmation){
          show_confirmation = false;
          $('#mi_success_popup').modal('show');
          $('#row-'+space_id+' .status-circle').removeClass('circle-red').removeClass('circle-yellow').addClass('circle-green');
        }
    });

    $('#mi_email_modal .modal-body .performance-mail-other span.toggle-cc').on('click', function(){
        $('#mi_email_modal .modal-body').find('.form-group.email-cc').toggleClass('hidden', 1000);
    });

    $('#mi_email_modal .modal-body .performance-mail-other span.toggle-bcc').on('click', function(){
        $('#mi_email_modal .modal-body').find('.form-group.email-bcc').toggleClass('hidden', 1000);
    });

    $('#mi_email_modal .modal-footer').on('click', 'button.send-email', function(){
      if(validateEmailPopupForm()){
          sendMIEmailAjax();
      }
    });


});

function changeOrder(reference,sort_order){
  $('.sorting td a span').removeClass('up down');
  $('.sorting td a').attr('sort_order','desc');
  reference.attr('sort_order','asc');
  reference.find('span').addClass('up').removeClass('down');
  if(sort_order == 'asc'){
    reference.attr('sort_order','desc');
    reference.find('span').addClass('down').removeClass('up');
  }
  if(sort_count === 3){
      reference.find('span').removeClass('up down');
      sort_count = 0;
  }

  resetMiAjax();
}

function resetMiAjax(){
  offset = 0;
  record_exist= false;
  miGetShares(offset,'reset');
}

function renderView(view_data,status){
  var source = $("#mi-share-record").html();
  var template = Handlebars.compile(source);
  var html=''; 
  count = view_data.spaces.length;
  if(count <= 50){
    $.each(view_data.spaces, function(index, space){
      if(typeof view_data.data.spaces[space] != 'undefined'){

        view_data.data.spaces[space].contract_end_date = dateFormat(view_data.data.spaces[space].contract_end_date);
        var progress_count = view_data.data.progress_bar[space].total;
        if(progress_count == 0) progress = constants.TASK_0_PROGRESS;
        else if(progress_count == 1) progress = constants.TASK_1_PROGRESS;
        else if(progress_count == 2) progress = constants.TASK_2_PROGRESS;
        else if(progress_count == 3) progress = constants.TASK_3_PROGRESS;
        else if(progress_count == 4) progress = constants.TASK_4_PROGRESS;
        else if(progress_count == 5) progress = constants.TASK_5_PROGRESS;
        else if(progress_count == 6) progress = constants.TASK_6_PROGRESS;
        else if(progress_count == 7) progress = constants.TASK_7_PROGRESS;
        else if(progress_count == 8) progress = constants.TASK_8_PROGRESS;

        html += template({
          'pending':view_data.data.pending_invites[space],
          'progress':progress,
          'space':space,
          'community': view_data.data.community[space],
          'posts': view_data.data.posts[space],
          'post_interactions': view_data.data.posts_intractions[space],
          'spaces': view_data.data.spaces[space],
          'space_data': view_data.spaces_data[space],
          'csi': view_data.data.csi[space],
          'nps':view_data.data.nps[space],
          'pending_tasks':view_data.data.progress_bar[space],
          'admin_names':view_data.user_data[space], 
          'community_growth':5,
          'csi_growth':50,
          'overall_growth':10,
          'contract_value_division':constants.MANAGEMENT_INFORMATION.contract_value_division,
        });
      }
    });
  }
  miRenderShares(html,status)

  $(".selectpicker").selectpicker();
}

function dateFormat( date ) {
   if(date){
      var today = new Date(date);
      var dd = today.getDate();
      var mm = today.getMonth()+1;

      var yy = today.getFullYear().toString().substr(-2);
      if(dd<10){
          dd='0'+dd;
      } 
      if(mm<10){
          mm='0'+mm;
      } 
      return today = mm+'/'+yy;
   }
   return false;
}

function addZeroes( num ) {
    var value = Number(num);
    var res = num.split(".");
    if(res.length == 1 || (res[1].length < 3)) {
        value = value.toFixed(2);
    }
    return value
}

function miRenderShares(html,status){
  if(status === 'reset') $(".mi-data-grid").find("tr.row-data").remove();
  $('.mi-data-grid').append(html);
  finished = 1;
  $("html:not(.legacy) table.table-header").stickyTableHeaders({fixedOffset: table_offset});
  offset = offset+parseInt(50);
  hide_columns_after_lazy_load();
}

function miGetShares(offset,status){
  var data = {
      'sort' : sort,
      'offset' : offset,
      'date_value': date_pick,
      'sort_order' : sort_order,
      'suppliers': suppliers.length ? suppliers:[''],
      'buyers': buyers.length ? buyers:[''],
      'RAG_filter': RAG_filter.length ? RAG_filter:[''],
      'status_filter' : status_filter.length ? status_filter:[''],
      'spaces_filter': shares.length ? shares:['']
  };
  if(!record_exist){
    record_exist = true;
     $.ajax({
      type: 'POST',
      dataType: 'JSON',
      url: baseurl+'/mi_ajax',
      data : data,
      async: true,
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      beforeSend: function(){
          hideLoader();
      },
      success: function(response){
        renderView(response,status);
      },complete:function(){
        $('.mi-overlay-div').addClass('hidden');
        record_exist = false;
        if(count < 50){
          record_exist = true;
        }
      }
    });
  }
}

function getMIRowData(space_id) {
  var row_data = {};
  var selected_row = $('#row-'+ space_id);
  row_data['buyer_name'] = selected_row.find('.buyer-col').text();
  row_data['seller_name'] = selected_row.find('.supplier-col').text();
  row_data['buyer_total'] = getInteger(selected_row.find('.buyer-community-col').text());
  row_data['buyer_change'] = getInteger(selected_row.find('.buyer-commonity-growth-col').text());
  row_data['seller_total'] = getInteger(selected_row.find('.sellers-community-col').text());
  row_data['seller_change'] = getInteger(selected_row.find('.sellers-community-growth-col').text());
  row_data['community_change'] = getInteger(selected_row.find('.overall-community-growth-col').text());

  row_data['posts_total'] = getInteger(selected_row.find('.overall-post-col').text());
  row_data['posts_change'] = getInteger(selected_row.find('.overall-post-growth-col').text());

  row_data['csi_total'] = getInteger(selected_row.find('.overall-csi-col').text());
  row_data['csi_change'] = getInteger(selected_row.find('.overall-csi-growth-col').text());

  return row_data;
}

function setTilesStats(row_data) {
    var tiles = email_modal.find('.performance-tiles-col');
    $(tiles).find('.customer-tile .performance-count-col').find('span:nth-child(1)').html(row_data['buyer_total']);
    $('input[name="community_buyers"]').val(row_data['buyer_total']);
    $(tiles).find('.customer-tile .performance-count-col').find('span:nth-child(2)').html(showColoredSign(row_data['buyer_change']));
    $('input[name="community_buyers_change"]').val(row_data['buyer_change']);

    $(tiles).find('.seller-tile .performance-count-col').find('span:nth-child(1)').html(row_data['seller_total']);
    $('input[name="community_sellers"]').val(row_data['seller_total']);
    $(tiles).find('.seller-tile .performance-count-col').find('span:nth-child(2)').html(showColoredSign(row_data['seller_change']));
    $('input[name="community_sellers_change"]').val(row_data['seller_change']);

    $(tiles).find('.posts-tile .performance-count-col').find('span:nth-child(1)').html(row_data['posts_total']);
    $('input[name="total_posts"]').val(row_data['posts_total']);
    $(tiles).find('.posts-tile .performance-count-col').find('span:nth-child(2)').html(showColoredSign(row_data['posts_change']));
    $('input[name="month_posts"]').val(row_data['posts_change']);

    $(tiles).find('.csi-tile .performance-count-col').find('span:nth-child(1)').html(row_data['csi_total']);
    $('input[name="csi_score"]').val(row_data['csi_total']);
    $(tiles).find('.csi-tile .performance-count-col').find('span:nth-child(2)').html(showColoredSign(row_data['csi_change']) + '%');
    $('input[name="csi_score_change"]').val(row_data['csi_change']);
}

function setEmailData(comm_type, space_id, row_change) {
  var row_data = getMIRowData(space_id);
  var case_type = 1;

  if((row_data['buyer_total'] > 0 && row_data['community_change'] < 1 && comm_type == 'community') || (row_data['posts_change'] < 5 && comm_type == 'posts') || (row_data['csi_change'] <= 0 && comm_type == 'csi'))
      case_type = 2;

  if(row_data['buyer_total'] === 0 && comm_type == 'community') 
        case_type = 3;

  var email_content = getEmailContent(comm_type, case_type); 
  email_content = email_content.replace(/\[admin\]/g, email_space_info.user.first_name.charAt(0).toUpperCase() + email_space_info.user.first_name.slice(1));
  email_content = email_content.replace(/\[name of share\]/g, email_space_info.share_name);
  email_content = email_content.replace(/\[Buyer Name\]/g, row_data['buyer_name']);
  email_content = email_content.replace(/\[X%\]/g, row_data['csi_change'] + '%');
  email_content = email_content.replace(/\[X\]/g, ((comm_type =='community') ? row_data['community_change'] : row_data['posts_change']));

  if(row_change){
    setTilesStats(row_data)
    $(email_modal).find('.modal-body  input[name="email_to"]').val(email_space_info.user.email);
    $(email_modal).find('.modal-body  input[name="email_subject"]').val(row_data['seller_name'] +' & '+ row_data['buyer_name']);
   }
  $(email_modal).find('.modal-body .message-area textarea').val(email_content);
}

function getEmailContent(comm_type, case_type) {
  var email_content = 'No Content available';
  if(comm_type == 'community'){
        email_content = "Hi [admin],\n\nGreat job! This month your [name of share] share community size has grown by [X].\n\nKeep up the good work!\n\nThe Client Share Team.";
      if(case_type === negative_email){
        email_content = "Hi [admin],\n\nYour [name of share] share community hasn’t grown this month.\n\nHere are 2 easy ways to grow your community:\n\n1. Ask your Customer to invite their colleagues.\n\n2. Make sure everyone involved in the account is invited.\n\nLet’s get you to the top of the leaderboard!\n\nThe Client Share Team.";
       }
      if(case_type === share_not_launched){
        email_content = "Hi [admin],\n\nIt looks like you haven't yet launched Client Share to [Buyer Name]\n\nBuyers and Suppliers are getting great results using Client Share, it's making their job easier and their relationship better. \n\nWhat’s holding you back? We’re here to help.\n\nThe Client Share Team.";
        }
    }

    if(comm_type == 'csi'){
       email_content = "Hi [admin],\n\nGreat job! This month, your [name of share] share average CSI score has increased by [X%].\n\nKeep up the good work!\n\nThe Client Share Team.";

      if(case_type === negative_email){
        email_content = "Hi [admin],\n\nThis month, your [name of share] share average CSI score has decreased by [X%]. The higher the score, the more engaged your client is with you and your team.\n\nHere are a few ways to improve your score:\n\n1. Share MI, proposals, thought leadership and presentations.\n\n2. Ask your leadership team to be sharing business updates.\n\n3. Grow your community by inviting more contacts.\n\nWe’re here to help.\n\nThe Client Share Team.";
        }
      }

      if(comm_type == 'posts'){

        email_content = "Hi [admin],\n\nGood job! This month, the number of posts in the [name of share] share has increased by [X].\n\nKeep up the momentum!\n\nThe Client Share Team.";

        if(case_type === negative_email){
          email_content = "Hi [admin],\n\nThis month, the number of posts in your [name of share] share is [X]. That’s low compared to industry standard.\n\nGartner says, the more you engage with your customer, the more likely you are to win; here's a few recommendations:\n\n1. Share Management Information, proposals, thought leadership and presentations.\n\n2. Get marketing to add relevant content for you.\n\n3. Ensure your leadership team are sharing management updates.\n\nWe’re here to help.\n\nThe Client Share Team.";
          }
      }

  return email_content;
}

function setEmailSpaceInfo(space_id){
    $.ajax({
      type: 'GET',
      dataType: 'JSON',
      async: false,
      url: baseurl+'/space_user_ajax?space_id='+space_id,
      success: function(result){
        email_space_info = result.result[space_id];
      },
    });
}

function validateEmailPopupForm(){
    var form = $('#edit_email');
    var form_valid = true;
    var message_input = form.find('textarea');
    if(message_input.val().trim() == ''){
       message_input.closest('div.form-group').addClass('error-empty');
       form_valid = false;
    }
    form.find('input[name="email_cc"]:hidden').val('');
    form.find('input[name="email_bcc"]:hidden').val('');
    form.find('input:visible').each(function(){
        var input_value = $(this).val().trim();
        var input_type = $(this).attr('type');
        var input_name = $(this).attr('name');
        if(input_type == 'email'){
           input_value = input_value.replace(/\s/g,"");
           $(this).val(input_value);
        }
        if((input_name == 'email_to' || input_name == 'email_subject') && (input_value == '' || typeof input_value == 'undefined')){
           $(this).closest('div.form-group').addClass('error-empty');
           form_valid = false;
        }
        else if(input_type == 'email' && input_value != '' && !validateMultipleEmails(input_value)){
           $(this).closest('div.form-group').addClass('error-email');
           form_valid = false;
        }
    });
    setTimeout(function(){
      form.find('div.form-group').removeClass('error-empty error-email');
    }, 5000);

    return form_valid;
  }

function validateMultipleEmails(string) {
    var regex = /^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9\-]+\.)+([a-zA-Z0-9\-\.]+)+([;]([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9\-]+\.)+([a-zA-Z0-9\-\.]+))*$/;
    return regex.test(string);
}

function sendMIEmailAjax(){
    $('.send-email').attr('disabled',true);
    var email_data = new FormData($('#edit_email')[0]);
    $.ajax({
          type: 'POST',
          url: baseurl+'/email',
          headers: {
            'cache-control': 'no-cache',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: email_data,
          async: true,
          beforeSend: function(){
            hideLoader();
          },
          success: function (response) {
            $('.send-email').attr('disabled',false);
            $('.mi-overlay-div').addClass('hidden');
             if(response.result){
                show_confirmation = true;
                email_modal.modal('hide');
             }else{
                $('.email-send-error').text('Error! can\'t send email, please try again.');
             }
          },
          error: function(response){
            $('.send-email').attr('disabled',false);
            alert('Something went wrong.');
          },
          cache: false,
          contentType: false,
          processData: false
        });
}

function getInteger(string){
  return parseInt(string.replace(/[\r\n\t|\n|\r\t\s]/g,""));
}

function showColoredSign(integer){
  var green_plus = '<span class="performance-up">+</span>';
  var red_minus = '<span class="performance-down">-</span>';
  var colored_signed = integer;

  if(parseInt(integer) > 0)
    colored_signed = green_plus + integer;

  if(parseInt(integer) < 0)
    colored_signed = red_minus + Math.abs(integer);

  return colored_signed;
}

$('.management-information-table thead.header th .remove-column').on('click', function (e) {
  $('*[data-uiclass="'+ $(this).parent().data('uiclass') +'"]').hide();
  hideLoader();  
  setTimeout(resetTableLayout, 500);
});

/*$('.management-information-table thead.header th .default_hide').each(function() {
  $('*[data-uiclass="'+ $(this).parent().data('uiclass') +'"]').hide();
  hideLoader();  
  setTimeout(resetTableLayout, 500);
});*/

function resetTableLayout(){
  set_heading_borders();
  updateColumnVisiblityDropdown();
  $('.mi-overlay-div').addClass('hidden');
}

function hide_columns_after_lazy_load() {
  $('.management-information-table thead').eq(0).find('th .remove-column').each( function () {
    
    var parent_of_remove = $(this).parent();
    if(!parent_of_remove.is(':visible')) {
      $('*[data-uiclass="'+ parent_of_remove.data('uiclass') +'"]').hide();
    }
  });
  set_heading_borders();
}

function set_heading_borders(offset) {
    $('thead th').removeClass('right-border-1');
    $('thead').eq(0).find('.community-th:visible').last().addClass('right-border-1');
    $('thead').eq(0).find('.csi-th:visible').last().addClass('right-border-1');
    $('thead').eq(0).find('.posts-th:visible').last().addClass('right-border-1');
    $('thead').eq(0).find('.pos-th:visible').last().addClass('right-border-1');
    $('thead').eq(0).find('.pi-th:visible').last().addClass('right-border-1');
    $('thead').eq(0).find('.pinv-th:visible').last().addClass('right-border-1');
    $('thead').eq(0).find('.prog-th:visible').last().addClass('right-border-1');
    $('thead').eq(0).find('.space-status-th:visible').last().addClass('right-border-1');
    set_data_column_borders(offset);
}

function set_data_column_borders(offset) {
    var selector_mi_row = $('.management-information-table thead.header th:visible');
    var community_index = selector_mi_row.index($('thead').eq(0).find('.community-th:visible').last());
    var csi_index = selector_mi_row.index($('thead').eq(0).find('.csi-th:visible').last());
    var posts_index = selector_mi_row.index($('thead').eq(0).find('.posts-th:visible').last());
    var pos_index = selector_mi_row.index($('thead').eq(0).find('.pos-th:visible').last());
    var pi_index = selector_mi_row.index($('thead').eq(0).find('.pi-th:visible').last());
    var pinv_index = selector_mi_row.index($('thead').eq(0).find('.pinv-th:visible').last());
    var prog_index = selector_mi_row.index($('thead').eq(0).find('.prog-th:visible').last());
    var space_status = selector_mi_row.index($('thead').eq(0).find('.space-status-th:visible').last());

    $('.management-information-table table.table-main tbody.mi-data-grid tr.row-data,.management-information-table table.table-main tr.sorting').each(function () {
        var row_data_children = $(this).children('td:visible');
        row_data_children.removeClass('right-border-1');
        row_data_children.eq(community_index).addClass('right-border-1');
        row_data_children.eq(csi_index).addClass('right-border-1');
        row_data_children.eq(posts_index).addClass('right-border-1');
        row_data_children.eq(pos_index).addClass('right-border-1');
        row_data_children.eq(pi_index).addClass('right-border-1');
        row_data_children.eq(pinv_index).addClass('right-border-1');
        row_data_children.eq(prog_index).addClass('right-border-1');
        row_data_children.eq(space_status).addClass('right-border-1');

    });
}