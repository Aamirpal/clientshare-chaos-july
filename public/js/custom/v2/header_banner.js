function updateHeaderS3FormData() {
  $.ajax({
    type: "GET",
    url: baseurl+'/s3_form_data',
    success: function( response ) {
      if(response.result){
        var s3_form = $('form#s3_form_details_header');
        setS3FormData(s3_form, response.s3_form_details);
      }
    },
    error: function(xhr, status, error) {
      logErrorOnPage(xhr, status, error, 'Appending S3 form data');
    }
  });
}

function upload_logo_file() {
    $('.save_executive_btn').prop('disabled', true);
    $('#upload_s3_file').trigger('click');
}

function getLogoCompany(element){
    var logo_edit_block = $(element).closest('div.upload-logo-name');
    var company = 'seller';
    if(logo_edit_block.hasClass('buyer')) company = 'buyer'
    if(logo_edit_block.hasClass('banner')) company = 'banner'
    return company;
}

function showLogoFileName(file_input){
  $('.banner-image-error').text('');
  var logo_preview_elements = $('.'+ $(file_input).attr('id') +'-preview');
  var selector_id = $(file_input).attr('id');
  var image_error_status = false;
  var file_name = '';
  if (file_input.files && file_input.files[0]) {
    file_name = file_input.files[0].name;
    var reader = new FileReader();
    reader.onload = (function(e) { 
        var img = new Image(); 
        img.src = e.target.result;
        img.onload = function() { 
          var height = this.height; 
          var width = this.width;
          var logo_selector= $(file_input).siblings('div.uploaded-logo-name');
          if(selector_id == 'share-banner' && (height < 128 || width < 1278)){
              $('.banner-image-error').text("Dimensions of uploaded banner image should be not less than 1280x128 pixels");
              logo_selector.find('span:first').html('');
              logo_selector.addClass('hidden');
              $(file_input).siblings('a.upload-logo').show();
              $(file_input).val('');
              $('.banner .upload-icon').show();
              e.preventDefault();
              image_error_status = true;
          } else if(selector_id == 'share-banner' && height > 150) {
              $('.banner-image-error').text("Height of uploaded banner image should not be less than 128 and greater than 150 pixels");
              logo_selector.find('span:first').html('');
              logo_selector.addClass('hidden');
              $(file_input).siblings('a.upload-logo').show();
              $(file_input).val('');
              $('.banner .upload-icon').show();
              e.preventDefault();
              image_error_status = true;
          }

          if(selector_id == 'share-banner' && (height > 128 || width > 1278)){
            $('.select-share-banner-part').removeClass('active');
            $('#banner-image, #edit-banner-image').val('');
            $('.share-banner-images').removeClass('select-share-banner-part');
          }
          function _base64ToArrayBuffer(base64) {
            var binary_string = window.atob(base64.split(",")[1]);
            var len = binary_string.length;
            var bytes = new Uint8Array(len);
            for (var i = 0; i < len; i++) {
              bytes[i] = binary_string.charCodeAt(i);
            }
            return bytes.buffer;
          }

          var exif = EXIF.readFromBinaryFile(_base64ToArrayBuffer(this.src));
          var canvas = document.createElement("canvas");
          canvas.width = this.width; 
          canvas.height = this.height;
          var canvas_context = canvas.getContext("2d");
          var x = y = 0;
          canvas_context.save(); 
          if (typeof exif.Orientation != "undefined") {
            switch (exif.Orientation) {
              case 2:
                // horizontal flip
                canvas_context.translate(canvas.width, 0);
                canvas_context.scale(-1, 1);
                break;
              case 3:
                // 180° rotate left
                canvas_context.translate(canvas.width, canvas.height);
                canvas_context.rotate(Math.PI);
                break;
              case 4:
                // vertical flip
                canvas_context.translate(0, canvas.height);
                canvas_context.scale(1, -1);
                break;
              case 5:
                // vertical flip + 90 rotate right
                canvas_context.rotate(0.5 * Math.PI);
                canvas_context.scale(1, -1);
                break;
              case 6:
                // 90° rotate right
                canvas_context.rotate(0.5 * Math.PI);
                canvas_context.translate(0, -canvas.height);
                break;
              case 7:
                // horizontal flip + 90 rotate right
                canvas_context.rotate(0.5 * Math.PI);
                canvas_context.translate(canvas.width, -canvas.height);
                canvas_context.scale(-1, 1);
                break;
              case 8:
                // 90° rotate left
                canvas_context.rotate(-0.5 * Math.PI);
                canvas_context.translate(-canvas.width, 0);
                break;
            }

            canvas_context.drawImage(img, x, y);
            canvas_context.restore();
            var final_image = canvas.toDataURL("image/jpeg", 1.0);
            var result =  final_image;
          } else { 
           var result = this.src;
          }
          if(!image_error_status) {
            logo_preview_elements.css({"background-image":"url('"+ result +"')"});
            $('.banner .upload-icon').hide();
          }
          if(image_error_status)
            $('.share-banner-images').addClass('select-share-banner-part');
        };
      });
    reader.readAsDataURL(file_input.files[0]);
  }
  if(image_error_status) return false;
  $(file_input).siblings('div.uploaded-logo-name').find('span:first').html(file_name);
  $(file_input).siblings('div.uploaded-logo-name').removeClass('hidden');
  $(file_input).siblings('a.upload-logo').hide();
}

function hideLogoFileName(file_input){
  var company = getLogoCompany(file_input);
  resetLogoPreview(company);
  $(file_input).val('');
  $(file_input).siblings('a.upload-logo').show();
  $(file_input).siblings('div.uploaded-logo-name').addClass('hidden');
  $(file_input).siblings('div.uploaded-logo-name').find('span:first').html('');
}

function setLogoPreview(company, image_url){
    $('.'+ company +'-logo-preview').removeAttr('style');
    $('.'+ company +'-logo-preview').css({"background-image":"url('"+ image_url +"')"});
}

function resetLogoPreview(company){
  var css_bg_image = "";
  var old_logo_url = $('input[name="'+company+'_logo_url"]').val();
  if(company == 'banner'){
    var old_logo_url = $('input[name="share_banner_url"]').val();
    $('.share-banner-images').addClass('select-share-banner-part');
  } 
  if(old_logo_url != '' && typeof old_logo_url != 'undefined') css_bg_image = "url('"+ old_logo_url +"')";
  if(company == 'banner') {
    $('.share-banner-preview').removeAttr('style').css("background-image", css_bg_image);
  }else{
    $('.'+ company +'-logo-preview').removeAttr('style').css("background-image", css_bg_image);
  }
}


function checkValidImageFile(file_input){
  var image_mime_types = ['image/gif', 'image/jpeg', 'image/png'];
  var error_message = $(file_input).siblings('div.image-error');
  if ($.inArray(file_input.files[0]['type'], image_mime_types) < 0) {
    error_message.removeClass('hidden');
    setTimeout(function(){ error_message.addClass('hidden'); }, 3000);
    return false;
  }
  return true;
}

function checkValidTwitterHandle(handle_value, twitter_input=""){
  $('.twitter-error').remove();
  var twitter_handle_regex = /^@[a-zA-Z0-9_]{1,15}$/i;
  if(handle_value.trim() == '@' || !twitter_handle_regex.test(handle_value)){
    twitter_input.closest('.twitter-input-col').append('<div class="email-error error-msg image-error twitter-error"> Please add valid twitter handle e.g. @handle </div>');
    return false;
  }
  return true;
}

$('document').ready(function(){
  updateHeaderS3FormData();
  $('.share-upload-logo-container,.select-share-banner-container,#update_welcome_share_logo').on('click', '.upload-logo', function(){
      if(!$(this).hasClass('disabled'))
          $(this).siblings('input[type="file"]').trigger('click');
  });

  $('.share-upload-logo-container,.select-share-banner-container,#update_welcome_share_logo').on('change', 'input[type="file"]', function(){
      if(this.files && this.files[0] && checkValidImageFile(this))
        showLogoFileName(this);
  });

  $('.share-upload-logo-container,.select-share-banner-container,#update_welcome_share_logo').on('click', '.uploaded-logo-name .remove-logo', function(){
      var file_input = $(this).closest('div.uploaded-logo-name').siblings('input[type="file"]');
      $('.banner .upload-icon').show();
      hideLogoFileName(file_input);
  });

  $(document).on('click', '.select-share-banner-part', function() {
      $('.select-share-banner-part').removeClass('active');
      $(this).addClass('active');
      var banner_url = $(this).attr('banner_url');
      $('#banner-image, #edit-banner-image').val(banner_url);
      css_bg_image = "url('"+ banner_url +"')";
      $('.share-banner-preview').removeAttr('style').css("background-image", css_bg_image);
  });

  $('form#edit_share_header button.save-share-header,form#update_welcome_share_logo .onboarding_company_logo, #update_welcome_share_banner .onboarding_company_logo')
      .on('click', function(){
      $('.share-name-error').text('');
      var share_name = $('#share-name').val().trim();
      if(share_name == '')
      {
        $('.share-name-error').text('Please add Share name.');
        return false;
      }
      var reference = $(this);
      var form_id = reference.closest("form").attr("id");
      var form = $('form#'+form_id);
      var form_data = new FormData(form[0]); 
      
      $.ajax({
        type: "POST",
        url: baseurl+'/share_header',
        data: form_data,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function(){
          $('.share-update-loader').removeClass('hidden');
        },
        success: function(response) {
            if(response.result){
              if(response.seller_logo.trim())
               $(document).find('li.space-pic-wrap span:first-child img').attr('src',response.seller_logo);

               if(response.buyer_logo.trim())
               $(document).find('li.space-pic-wrap span:nth-child(2) img').attr('src',response.buyer_logo);

              if(response.background_image.trim())
              $(document).find('.top-navbar').css({"background-image":"url('"+ response.background_image +"')"});
              
              if(response.space_data.share_name){
              $(document).find('#shareDropdown').text(response.space_data.share_name);
              $(document).find('.share-name-show .s_name a').text(response.space_data.share_name);
              $('ul.dropdown-menu a[href="'+baseurl+'/clientshare/'+current_space_id+'"]').find('span.share-name').text(response.space_data.share_name);
              }
              if(form_id == 'update_welcome_share_logo' || form_id == 'update_welcome_share_banner')
              {
                  current_step = reference.closest('.welcome-cs-popup');
                  updateTourStep(current_step.attr('data-step'));
                  current_step.addClass('hidden');
                  current_step.next($('.welcome-cs-popup')).removeClass('hidden');
              }
              else
              {
                $('#share_logo_edit').modal('hide');
              }
              addProgressBarsection();
              $('.edit_share_header').trigger('reset');
            } else if(!response.result) {
                $(document).find('.'+response.key).addClass('has-error');
                $(document).find('.'+response.key).find('.twitter-error').remove();
                $(document).find('.'+response.key).parent().append('<div class="email-error error-msg image-error twitter-error">'+response.error+'</div>');
            }
        },
        error: function(xhr, status, error){
          logErrorOnPage(xhr, status, error, 'something went wrong while saving header data! please try again');
        },
        complete:function(){
          $('.share-update-loader').addClass('hidden');
        },
        cache: false,
        contentType: false,
        processData: false
      });
  });

  $('form#edit_share_header button.share-header-cancel').on('click', function(){
      location.reload();
  });


  $('form#edit_share_header .twitter-feed-input,form#update_welcome_share_logo .twitter-feed-input').on('change', function(){
      var twitter_input = $(this);
      var twitter_handle = twitter_input.val();
      twitter_input.removeClass('error');
      var logo_edit_block = twitter_input.closest('div.upload-logo-name');
      var company = getLogoCompany(twitter_input);
      twitter_handle = twitter_handle.trim();

      if(twitter_handle != '' && typeof twitter_handle != 'undefined'){
        twitter_input.siblings('span.remove-handle').removeClass('hidden');
        logo_edit_block.find('a.upload-logo').addClass('disabled');
        setTimeout(function(){
            if(checkValidTwitterHandle(twitter_handle, twitter_input)){
              hideLogoFileName(logo_edit_block.find('input[type="file"]'));
              setLogoByTwitterHandle(twitter_handle, twitter_input);
             }
          }, 500);
      }
      else{
          removeTwitterHandle(twitter_input);
      }
   });

  $('form#edit_share_header,form#update_welcome_share_logo').on('click', 'span.remove-handle', function(){
      removeTwitterHandle($(this).siblings('.twitter-feed-input'));
      $(this).parent().find('input').removeClass('has-error');
      $(this).parent().find('twitter-error').remove();
      var seller_twitter_name = $('.seller_twitter_name').val().trim();
      var buyer_twitter_name = $('.buyer_twitter_name').val().trim();
      if(seller_twitter_name == '' && buyer_twitter_name == '') {
          $('.twitter-input-col input').removeClass('has-error');
          $('.twitter-error').remove();
          $('.onboarding_company_logo').css('pointer-events','auto');
      }
  });

});

function setLogoByTwitterHandle(twitter_handle, element){
  var company = getLogoCompany(element);
  var logo_edit_block = $(element).closest('div.upload-logo-name');
  $(logo_edit_block).find('.twitter-input-col input').removeClass('has-error');
  $('.onboarding_company_logo').css('pointer-events','auto');
  $.ajax({
        type: "GET",
        url: baseurl+'/twitter_api?request_url=users/show&option[screen_name]='+twitter_handle,
        cache: false,
        beforeSend: function(){
          $('.share-update-loader').removeClass('hidden');
        },
        success: function(response) {
            if(response.result){
              $('.share-update-loader').addClass('hidden');
              var twitter_profile_image = response.data.profile_image_url_https;
              twitter_profile_image = twitter_profile_image.replace('_normal', '');
              logo_edit_block.find('input.twitter-logo-url').val(twitter_profile_image);
              setLogoPreview(company, twitter_profile_image);
            }else{
              $(logo_edit_block).find('.twitter-input-col input').addClass('has-error');
              $('.onboarding_company_logo').css('pointer-events','none');
              $('.twitter-error').remove();
              $(logo_edit_block).find('.twitter-input-col').append('<div class="email-error error-msg image-error twitter-error">'+response.error+'</div>');
            }
        },
        error: function(xhr, status, error){
          logErrorOnPage(xhr, status, error, 'Error occurred while fetching logo via twitter');
        },
        complete:function(){
          $('.share-update-loader').addClass('hidden');
        }
    });
}

function removeTwitterHandle(element){
    var company = getLogoCompany(element);
    var logo_edit_block = $(element).closest('div.upload-logo-name');
    resetLogoPreview(company);
    $(element).val('');
    $(element).siblings('span.remove-handle').addClass('hidden');
    logo_edit_block.find('input.twitter-logo-url').val('');
    logo_edit_block.find('a.upload-logo').removeClass('disabled');
}
