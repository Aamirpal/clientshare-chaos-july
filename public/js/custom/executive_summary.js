function getExecutiveSummary(){
  $.ajax({
      type: "GET",
      url: baseurl+'/get_executive_summary/'+current_space_id,
      success: function( response ) {
          if(response.result){
              var files_count = response.media.length;
              if(files_count >= 2)
                  $('div.upload_doc_col').hide();
              else{
                  $('div.upload_doc_col').show();
              }
              
              setSummaryPreviewData(response);
              setSummaryEditData(response);
              setExecSummarayS3FormData(response);
          }

      },
      error: function(response){
         var error = '<p class="alert-danger">Error while loading files. </p>'
         $('div.summary-links').prepend(error);
         $(error).delay('1000').remove();
      }
  });
}

function setSummaryPreviewData(response){
  var links_array = $('div.summary-links').find('.executive-file');
  var executive_selector = $('.executive_show_less,.executive_show_more a');
  if(response.media.length === 0){
   $('.executive_show_more').css('display','block');
   $('.tile-description').addClass('executive-center');
   executive_selector.hide();
  }else{
    executive_selector.show();
  }
  $.each(response.media, function(media_key, media_value){
   if($.inArray(media_value.media_type.toLowerCase(), doc_extension) != -1 || $.inArray(media_value.media_type.toLowerCase(), video_extension) != -1)
   {
      if(media_value.media_path != '' && typeof media_value.media_path != 'undefined' && typeof links_array[media_key] != 'undefined')
      {
        var current_link = links_array[media_key];
        var file_name = media_value.metadata.originalName.split(/\.(?=[^\.]+$)/);
        media_value.metadata.originalName = encodeURIComponent(media_value.metadata.originalName);
        $(current_link).find('a').children('span').text('');
        $(current_link).find('a').children('span').prepend(file_name[0]);
        $(current_link).find('input[name="url_src"]').val(media_value.media_path);
        $(current_link).find('a img').attr('src', baseurl + media_icons[media_value.media_type.toLowerCase()]);
        $(current_link).find('a').attr('onclick', 'viewExecutiveAttachment("'+media_value.media_path+'", "'+media_value.metadata.mimeType+'", "'+media_value.metadata.originalName.replace(/(['"])/g, "\\$1")+'", "'+media_value.metadata.size+'")');
        $(current_link).show();
      }
    }
  });
}

function viewExecutiveAttachment(url, url_type, file_name, file_size) {
    viewAttachment(url, url_type, file_name, file_size);
}

function setSummaryEditData(response){
  var files_count = response.media.length;
  $('form#executive_summary_save').find('input.post-media-data').val(files_count);
  $('form#welcome_executive_summary_save').find('input.post-media-data').val(files_count);
  files_array = $('form#executive_summary_save').find('div.remove_executive_file');
  onboarding_files_array = $('form#welcome_executive_summary_save').find('div.remove_executive_file');

  $.each(response.media, function(media_key, media_value){
      if($.inArray(media_value.media_type.toLowerCase(), doc_extension) != -1 || $.inArray(media_value.media_type.toLowerCase(), video_extension) != -1){
          if(media_value.media_path != '' && typeof media_value.media_path != 'undefined' && typeof files_array[media_key]  != 'undefined')
          {
              var current_file = files_array[media_key];
              setExecutiveFilesInPopup(media_value, video_extension, current_file);
          }

          if(media_value.media_path != '' && typeof media_value.media_path != 'undefined' && typeof onboarding_files_array[media_key]  != 'undefined')
          {
              var current_file = onboarding_files_array[media_key];
              setExecutiveFilesInPopup(media_value, video_extension, current_file);
          }
      }
  });
}

function countCharExec(val) {
        $(val).removeClass('has-error');
        var len = val.value.length;
        var bio_count = $('.character_number').val();
        $('.character_number').text(bio_count + len);
};


function countExecutiveCharExec(val) {
        $(val).removeClass('has-error');
        var len = val.value.length;
        var bio_count = $('.executive_character_number').val();
        $('.executive_character_number').text(bio_count + len);
};

function renderExecutiveAttachments(){
    $.ajax({
        type: "GET",
        url: baseurl+'/get_executive_summary/'+current_space_id,
        success: function( response ) {
            if(response.result){
                var files_count = response.media.length;
                if(files_count >= 2)
                    $('div.upload_doc_col').hide();
                else
                    $('div.upload_doc_col').show();
                
                setSummaryPreviewData(response);
                $('.tile-description').removeClass('executive-center');
            }

        },
        error: function(response){
           var error = '<p class="alert-danger">Error while loading files. </p>'
           $('div.summary-links').prepend(error);
           $(error).delay('1000').remove();
        }
    });
}

function setExecutiveFilesInPopup(media_value, video_extension, current_file){
    var file_name = media_value.metadata.originalName.split(".");
    $(current_file).children('span:nth-child(2)').html(decodeURIComponent(file_name[0]));
    $(current_file).find('img.delete_summary_files').attr('id', media_value.id);
    if($.inArray(media_value.media_type.toLowerCase(), video_extension) != -1)
        $(current_file).prev('input.already_uploaded_video_file').val(encodeURIComponent(file_name[0]));
    else
        $(current_file).prev('input.already_uploaded_pdf_file').val(encodeURIComponent(file_name[0]));
    $(current_file).show();
}

function setExecSummarayS3FormData(response){
  var s3_form = $('form#s3_form_details');
  s3_form.attr('action', response.s3_form_details.url);
  var form_inputs = '';
  $.each(response.s3_form_details.inputs , function(input_name, input_value){
      form_inputs += '<input type="hidden" name="'+ input_name.toLowerCase() +'" value="'+ input_value +'">';
  });
  s3_form.prepend(form_inputs);
}

$('document').ready(function(){
  var executive_modal_content = '';
  getExecutiveSummary();

  $('#executive_modal').on('shown.bs.modal', function () {
     executive_modal_content  = $(this).find('.modal-content').html();
  });

  $('#executive_modal').on('hidden.bs.modal', function () {
      $(this).find('.modal-content').html(executive_modal_content);
  });
  
});