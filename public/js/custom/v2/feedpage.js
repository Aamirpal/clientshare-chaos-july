var get_all_share_notifications; let notification_count; let activity_notification; let get_global_search; let
  get_profile_info;
var isScroll = null;
let mainNotificationRecord = [];
var path_name = 'clientshare';

if(window.location.pathname == "/" || window.location.pathname == "")
  window.history.pushState(null, null, '/clientshare/'+current_space_id);

function getAllShareNotifications() {
  $totalNotifications = 0;
  if (get_all_share_notifications != null || get_all_share_notifications == 'error') return;

  get_all_share_notifications = $.ajax({
    type: 'GET',
    url: baseurl+'/share-notifications/'+current_space_id+'/'+loggin_user_id,
    contentType: 'application/json',
    dataType: 'json',
    success:function(response) {
      $.each(response.data, function(i, item) {
        if (item.count > 0) {
          $('#shareNoti_'+item.space_id).show();
          $('#shareNoti_'+item.space_id).html(item.count.toString().length > 2 ? '99+' : item.count);
        }
        $totalNotifications += item.count;
      });
      if ($totalNotifications > 0) {
        $('.allnotifications').show();
      }
      get_all_share_notifications = null;
    },
    error:function() {
      get_all_share_notifications = 'error';
    },
  });
}

function searchShare() {
    var searchInput, filter, shareList, arrayShare, currentShare, index;
    searchInput = document.getElementById("search_share");
    filter = searchInput.value.toUpperCase();
    shareList = document.getElementById("search_share_ul");
    arrayShare = shareList.getElementsByTagName("li");
    for (index = 0; index < arrayShare.length; index++) {
        currentShare = arrayShare[index].getAttribute('value');
        if (currentShare.toUpperCase().indexOf(filter) > -1) {
            arrayShare[index].style.display = "";
        } else {
            arrayShare[index].style.display = "none";

        }
    }
}

function debounce(func, wait, immediate) {
	var timeout;
	return function() {
		var context = this, args = arguments;
		var later = function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		var callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	};
};

function logErrorOnPage(xhr, status, error, uid) {
  try {
    logError(xhr, status, error, uid);
  } catch(error) {
    console.log(error);
  }
}
var getGlobalSearch = debounce(function(keyword) {
  count = 1;
  if($.trim(keyword) == ""){
    $('.search-dropdown').empty();
    $('.search-dropdown').hide();
    $('.search-loader').hide();
    return false;
  }
  get_global_search = $.ajax({
    type: 'GET',
    url: baseurl+'/global-search/'+keyword+'/'+current_space_id+'/'+loggin_user_id+'/'+count,
    contentType: 'application/json',
    dataType: 'json',
    beforeSend: function() {
      $('.search-loader').show();
    },
    success: function(response) {
      const result = response.data.posts || [];
      getSearchResult(result,keyword);
    },
    error: function() {
      get_all_share_notifications = 'error';
    },
    complete: function() {
      $('.search-loader').hide();
    }
  });
  $("body").click(function(e) {
    $('.search-dropdown').hide();
  });
}, 250);

function updateViaLinkedIn(){
      if(($(document).find('#linkedin_info').val().length) > 0){ 
            var linked_information = JSON.parse($(document).find('#linkedin_info').val());
            if(linked_information.linkedin){
            $(document).find('#user_job_title').val(linked_information.linkedin.user.positions.values[0].title);       
            $(document).find('#user_bio').val(linked_information.linkedin.user.headline);
            $(document).find('#user_linkedin').val(linked_information.linkedin.user.publicProfileUrl);
            $(document).find(".linkdin-profile-btn").addClass('hide');;
            }
      }
}
var request_in_progress = false;
function getProfileInfo() {
    if(!request_in_progress){
    get_profile_info = $.ajax({
        type: 'GET',
        url: baseurl+'/get-profile/'+current_space_id,
        contentType: 'application/json',
        dataType: 'json',
        beforeSend: function () {
            request_in_progress = true;
            $('.custom-loader').show();
        },
        success: function(response) {
            $(document).find('#user_job_title').val(response.data.job_title);
            $(document).find('#user_bio').val(response.data.bio);
            const user_contact = JSON.parse(response.data.contact);
            $(document).find('#user_linkedin').val(user_contact.linkedin_url);
            $(document).find('#user_phone_number').val(user_contact.contact_number);
            if(!user_contact.linkedin_url){
                $('.linkdin-profile-btn').removeClass('hide').parent().removeClass('profile-btn-right');
            }
            if((linkedin_data == 'yes' || buyer == 'yes' || linked_in == 'yes') && !user_contact.linkedin_ur){
                updateViaLinkedIn();
                window.history.pushState(null, null, baseurl  + '/clientshare/'+current_space_id);
            }
            
            const charLen = $('#user_bio').val().length;
            $(document).find('#total_char').text(charLen);

            $(document).find('#first_name').text(response.data.first_name);
            $(document).find('#user_full_name').text(response.data.first_name+' '+response.data.last_name);
            $(document).find('#user_first_name').val(response.data.first_name);
            $(document).find('#user_last_name').val(response.data.last_name);
            $(document).find('#user_email').val(response.data.email);

            $(document).find('#first_name_prev').val(response.data.first_name);
            $(document).find('#last_name_prev').val(response.data.last_name);

            if (response.data.company) {
                $(document).find('#user_community, #user_company').val(response.data.company.company_name);
                $(document).find('#user_company_id').val(response.data.company.id);
            }
            if (response.data.sub_company !== null) {
                $(document).find('#user_sub_company').attr('name', 'sub_company').val(response.data.sub_company.company_name);
            }
            if(response.data.sub_company !== null){
                $(document).find('.sub-company-div').removeClass('hide').parent().removeClass('profile-btn-right');
            }
            $(document).find('#user_space_id').val(current_space_id);
        },
        complete: function () {
             request_in_progress = false;
             $('.custom-loader').hide();
        },
        error: function (xhr, status, error) {
                logErrorOnPage(xhr, status, error, 'Profile info');
        }, 
   });
    }
}

$(document).ready(function() {
  var $document = $(document), previousScrollTop = 0, scrollLock = false;
  $('.nav-dropdown-link').on('click', function(){
    $('body').toggleClass('body-scrollbar');
  });

  $('#global-search-box').on('click',function(){
    $('body').addClass('body-scrollbar');
  })


  $(document).on('click', function(e) {
    let elementClass = $(this).get()[0].activeElement.className
    let elementId = $(this).get()[0].activeElement.id;
    if(elementId !== 'global-search-box' && elementId !== 'search_share' ||(elementClass === 'v2-clientshare body-scrollbar')){
      $('body').removeClass('body-scrollbar');
      $('#global-search-box').val("");
      $('.no-result').hide();
      $('.search-dropdown').hide();
     }
   if($(this).get()[0].activeElement.id !== 'search_share'){
    $('#search_share').val("");
   }
    
  });

  $('#shareDropdown').on('click',function(){
    searchShare()
  })
  

  getAllShareNotifications();
  global_search_box = $('#global-search-box');
  //getGlobalSearch(global_search_box.val());
  global_search_box.on('keyup', function () {
    $('.search-loader').show();
    getGlobalSearch($(this).val());
  });
  $('#show_user_profile, #user_profile_popup').on('click', function() {
    $(document).find('#user_profile').modal('show');
    $('#sub_comapany_suggesstion').hide();
    getProfileInfo();
  });

  $(document).find('#user_profile').on('hidden.bs.modal, show.bs.modal', function (e) {
    $('body').removeClass('body-scrollbar');
  });

  $(document).on('change', '#company_admin', function () {
        var buyer_company_id = $(this).find(":selected").val();
        var buyer_company = $(this).find(":selected").text();
        var check_sub_comp_status = $('.buyer_info_hidden').attr('sub-comp-active');
        $("#user_company").val(buyer_company);
        $("#user_company_id").val(buyer_company_id);
        if (check_sub_comp_status == 1) {
            var buyer_company_hidden_id = $('.buyer_info_hidden').attr('buyer-id');
            var buyer_company_hidden_name = $('.buyer_info_hidden').val();
            if (buyer_company_hidden_id == buyer_company_id && buyer_company_hidden_name == buyer_company) {
                $('.sub-company-div ').removeClass("hide");
                $('#user_sub_company').attr('name', 'sub_company');
                $('#user_sub_company').addClass('c_side_validation');
                
            } else {
                $('#user_sub_company').removeAttr('name');
                $('.sub-company-div ').addClass("hide");
                $('#user_sub_company').removeClass('c_side_validation');

            }
        }
    });
});

function getSearchResult(data,keyword) {
  const source = $('.search_list_handle_bar').html();
  const template = Handlebars.compile(source);
  let html = '';
  template(data)
  data.forEach(function(item) {
    var temp=[];
    temp['post_description'] = item.post_description.replace(new RegExp(keyword, "ig"),'<span class="track-word">'+keyword+'</span>')
    temp['post_subject'] = item.post_subject.replace(new RegExp(keyword, "ig"),'<span class="track-word">'+keyword+'</span>')
    html += template(Object.assign({}, temp));
  });
  if(html.trim() == ''){
    html = '<li><span class="no-result">No result found!<span></li>';
  }
  $('.search-dropdown').html(html);
  
    $('.search-dropdown').show();
  
}


$('.edit-name-icon').on('click', function(e) {
  $('.user-name').hide();
  $('.edit-user-name').show();
});

$('.cancel-user-name').on('click', function(e) {
  $('.user-name').show();
  $('.edit-user-name').hide();
  $('#user_first_name').val($('#first_name_prev').val());
  $('#user_last_name').val($('#last_name_prev').val());
});


function uploadProfilePicture(input) {
  if (input.files && input.files[0]) {
    const fileinput = document.getElementById('show_profile_pic');
    if (!fileinput) { return ''; }
    const filename = fileinput.value;
    if (filename.length === 0) { return ''; }
    const dot = filename.lastIndexOf('.');
    if (dot === -1) { return ''; }
    const extension = filename.substr(dot, filename.length);
    const file_ext = extension.toLowerCase();
    const allowed_extensions = ['.jpg', '.png', '.bmp', '.gif', '.jpeg'];
    const a = allowed_extensions.indexOf(file_ext);
    if (a < 0) {
      $('#show_profile_pic').val('');
      alert('Please Select Image');
      return false;
    }
    const reader = new FileReader();
    // reader.onload = function (e) {
    reader.onload = (function (e) {
      /** *******************New Code Start******************* */
      const img = new Image();
      img.src = e.target.result;
      img.onload = function () {
        function _base64ToArrayBuffer(base64) {
          const binary_string = window.atob(base64.split(',')[1]);
          const len = binary_string.length;
          const bytes = new Uint8Array(len);
          for (let i = 0; i < len; i++) {
            bytes[i] = binary_string.charCodeAt(i);
          }
          return bytes.buffer;
        }

        const exif = EXIF.readFromBinaryFile(_base64ToArrayBuffer(this.src));
        const canvas = document.createElement('canvas');
        canvas.width = this.width;
        canvas.height = this.height;
        const ctx = canvas.getContext('2d');
        const x = 0;
        const y = 0;
        ctx.save();
        if (typeof exif.Orientation !== 'undefined') {
          switch (exif.Orientation) {
            case 2:
              // horizontal flip
              ctx.translate(canvas.width, 0);
              ctx.scale(-1, 1);
              break;
            case 3:
              // 180° rotate left
              ctx.translate(canvas.width, canvas.height);
              ctx.rotate(Math.PI);
              break;
            case 4:
              // vertical flip
              ctx.translate(0, canvas.height);
              ctx.scale(1, -1);
              break;
            case 5:
              // vertical flip + 90 rotate right
              ctx.rotate(0.5 * Math.PI);
              ctx.scale(1, -1);
              break;
            case 6:
              // 90° rotate right
              ctx.rotate(0.5 * Math.PI);
              ctx.translate(0, -canvas.height);
              break;
            case 7:
              // horizontal flip + 90 rotate right
              ctx.rotate(0.5 * Math.PI);
              ctx.translate(canvas.width, -canvas.height);
              ctx.scale(-1, 1);
              break;
            case 8:
              // 90° rotate left
              ctx.rotate(-0.5 * Math.PI);
              ctx.translate(-canvas.width, 0);
              break;
          }

          ctx.drawImage(img, x, y);
          ctx.restore();
          const finalImage = canvas.toDataURL('image/jpeg', 1.0);
          var result = finalImage;
        } else {
          var result = this.src;
        }
        $('#show_changed_profile_pic').css('background-image', 'url('+result+')');
      };
    });
    reader.readAsDataURL(input.files[0]);
    $('.show_profile_pic').hide();
    $('#show_changed_profile_pic').show();
  }
}


function textAreaAdjust(o) {
  o.style.height = '1px';
  o.style.height = 1 + o.scrollHeight+'px';
}

function charCount(val) {
  const len = val.value.length;
  const cnt = $('#total_char').val();
  $('#total_char').text(cnt + len);
}

function selectComp(val) {
  $('.sub-company-input').val(val);
  $('#sub_comapany_suggesstion').hide();
}

$(document).on('keyup', '.sub-company-input', function () {
  const subCompany = $(this).val();
  $('.sub-company-input .client_side_validation_msg').hide();
  const space_id = $('#user_space_id').val();
  $.ajax({
    type: 'GET',
    dataType: 'html',
    url: baseurl+'/search_sub_comp?comp='+subCompany.trim()+'&'+'space_id='+space_id,
    success:function(data) {
      $('#sub_comapany_suggesstion').show();
      $('#sub_comapany_suggesstion').html(data);

      $('.sub_comp_add_list').html('Add '+ subCompany).css('color', '#2CCDA0');
      $('.sub-company-input').css('background', '#FFF');
      const subCompanyLength = $('.sub-company-hidden-input').length;
      if (subCompanyLength == '1') {
        $('.sub_comp_add_list').hide();
      }
    },
  });
});

$(document).on('click', '.sub_comp_add_list', function() {
  $('#sub_comapany_suggesstion').hide();
});
$(document).click(function (event) {
    if (!$(event.target).parents("#sub_comapany_suggesstion").length) {
        $('#sub_comapany_suggesstion').hide();
    }
});
$(document).on('click', '.tourlink_yes_linkedin', function () {
  $('.custom-loader').show();
  setLinkedinSession();   
});

function setLinkedinSession(){
  if( $('#tested123').length ) {
      var company = $('#tested123').val();
  }else{
      var company = '';
  }
  if( $('.sub-company-input').length ) {
      var sub_company = $('.sub-company-input').val();
  }else{
      var sub_company = '';
  }
  var company_status = 0;
  if( $('.buyer_info_hidden').length ) {
    var buyer_company_hidden_id = $('.buyer_info_hidden').attr('buyer-id');
    var buyer_company_hidden_name = $('.buyer_info_hidden').val();
    var buyer_company_id = $('.company_admin').find(":selected").val();
    var buyer_company = $('.company_admin').find(":selected").text();
    if (buyer_company_hidden_id == buyer_company_id && buyer_company_hidden_name == buyer_company) {
      var company_status = 1;
    }
  }
  var job_title = $('#user_job_title').val();
  var biotext = $('#user_bio').val();
  var linkedin_link = $('#user_linkedin').val();
  if( $('#user_phone_number').length ) {
   var phone_no = $('#user_phone_number').val();
  }else{
   var phone_no = '';
  }  
  if (company != '' || sub_company != '' || job_title !='' || biotext !='' || linkedin_link !='' || phone_no !=''){
    $.ajax({
      type: "GET",
      cache:false,
      async:true,
      url: baseurl+'/set_linkedin_session',
      data : {company:company,sub_company:sub_company,job_title:job_title,
          biotext:biotext,linkedin_link:linkedin_link,phone_no:phone_no,company_status:company_status},
      success: function(data) {
        window.location = baseurl + "/auth/linkedin";
      },
      error: function(error) {
        console.log(error);
      }
    });
  }else{
    window.location = baseurl + "/auth/linkedin";
  }
}
function flashValidationMessage(selector, message, delay) {
    return $(selector).html(message).show().delay(delay).fadeOut("slow");
}

$(document).on('click', '.update-user-profile', function (e) {
    e.preventDefault();
    var job_title = $('#user_job_title').val().length;
    var company_name = ($('#company_admin').length) ? $("#company_admin option:selected").val().length : $("#user_company").val().length;
    var sub_company_name =  ($('#user_sub_company').is(":visible")) ? $("#user_sub_company").val().length :'not-exists';
    var first_name = $('#user_first_name').val().length;
    var last_name = $('#user_last_name').val().length;
    var error_count = 0;
    if (job_title < 1) {
        flashValidationMessage('.job-title-error', 'Field is Required', 800);
        error_count++;
    }
    if (company_name < 1) {
        $('#sub_comapany_suggesstion').hide();
        flashValidationMessage('.company-error', 'Field is Required', 800);
        error_count++;
    }
    if ( sub_company_name !=='not-exists' && sub_company_name < 1) {
        $('#sub_comapany_suggesstion').hide();
        flashValidationMessage('.sub-company-error','Field is Required', 800);
        error_count++;
    }

    if (first_name < 1) {
        flashValidationMessage('.first-name-error','Field is Required', 800);
        error_count++;
    } else if (first_name > 25) {
        flashValidationMessage('.first-name-error','The first name cannot be greater than 25 characters', 800);
        error_count++;
    }

    if (last_name > 25) {
        flashValidationMessage('.last-name-error','The last name cannot be greater than 25 characters', 800);
        error_count++;
    } else if (last_name < 1) {
        flashValidationMessage('.last-name-error','Field is Required', 800);
        error_count++;
    }
    if (error_count < 1) {
        $(document).find("#update_profile_form").submit();
        $(document).find('#user_profile').modal('hide');
    }
});
$('.phone-number-validate').on('keyup paste', function () {
    position = this.selectionStart;
    this.value = this.value.replace(/[^ 0-9+(),-.]/g, '');
    this.selectionEnd = position;
});
