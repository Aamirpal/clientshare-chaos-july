path_name = 'clientshare';
var temp_key = '';
var feed_xhr = null;
var content_found = sticky_sidebar_stop = true;
var post_category;
var single_post_data;
var post_null = false;
var post_feed_data = {
  'is_scroll': 1,
  'offset':0,
  'space_id':$('.space_id_hidden').val()
};
var counter = 0;
  
$(window).bind('scroll', function() {
  post_feed_data['is_scroll'] = 1;
  if($(window).scrollTop() >= $('#load_more_post').offset().top + $('#load_more_post').outerHeight() - window.innerHeight - 10 && content_found === true && !feed_xhr){
    getFeed();
  }
});

function feedbackPopup(){
  rating = getQueryVariable('feedback_rating');
  if(!rating) return;
  $.ajax({
    type: 'POST',
    dataType: 'html',
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    data: {'rating':rating},
    url: baseurl+'/feedback_popup/'+session_space_id,
    success: function(responce){
      $('#feedback-popup').remove();
      $('.post-modal').append(responce);
      $('#feedback-popup').find('input:radio[value='+rating+']').trigger('click');
      $('#feedback-popup').modal('show');
    }
  });
}

$('.executive_show_less,.executive_show_more a').hide();
$(document).ready(function(){
  var check_feedback_status = $('.check_feedback_on_off_status').val();
  if(check_feedback_status == 1)
      $('.give-feedback-buyer').trigger('click');  

  if(single_post_id)
    $('.single-post-popup .post-feed-section > iframe').remove();

  if(feedback_flag)
    $('#feedback-popup').modal('show');

  feedbackPopup();
  
  $(document).on('click','span.more-attachments', function(){
    $(this).closest('.post-block').find('.findmedia.hiddenable').toggleClass('hidden');
    $(this).closest('.post-block').find('.more-attachments').toggleClass('hidden');
  });

  $('[data-toggle="popover"]').popover();
  $(document).on('click','.dropdown-toggle',function(){
    $('.popover').css('display', 'none', 'important');
  });

   $(document).on('mouseover','.dropdown-toggle',function(){
       if($(".visible-dropdown-view").hasClass('open')){
          $('.popover').css('display', 'none', 'important');
       }
   });

   $(document).on('mouseover','.visible-setting',function(){
       if($(".visible-dropdown-view").hasClass('open')){
          $('.popover').css('display', 'none', 'important');
       }
   });

  $(document).on('click','.filter_post_category',function(){
    post_null = false;
    post_feed_data['is_scroll'] = 0;
    post_feed_data['offset'] = 0;
    post_feed_data['category'] = $(this).attr('key');
      if(temp_key != $(this).attr('key')){
        $('.filter-select').removeClass('category-selected filter-select').addClass('disable');
      }
      if ( $( this ).hasClass( "category-selected" ) ) {
        $(this).removeClass('category-selected').addClass('disable');
      post_feed_data['category'] = '';
        temp_key = '';
      }else{
        $(this).removeClass('disable').addClass('category-selected filter-select');
        temp_key = $(this).attr('key');
      }
    sticky_sidebar_stop = true;
    refreshFeed();
  });

  $(document).on('click','.show_extra_content',function(){
      var full_description = $(this).closest('.post-description').find('.full_description');
      full_description.removeClass('hidden').show();
      $(this).closest('.post-description').find('.trim_description').addClass('hidden');
  });

  $(document).on('click','.show_less_content',function(){
    $(this).closest('.post-description').find('.trim_description').removeClass('hidden').show();
    $(this).closest('.post-description').find('.full_description').addClass('hidden');
  });

  $(document).on('paste', '.comment-add-section div[contenteditable="true"]', function(event){
      pasteAsPlainText(this, event);   
  });

});

function endorseUserWrap(user){
  return '<a style="text-decoration:none; color:#0D47A1" href="#!" onclick="liked_info(this);" data-id="'+user['id']+'">'+user['fullname']+'</a>';
}

function endorseUsersList(users){
    var user_list =  '';
    $.each(users, function(user_index, user){
      user_list += user['user']['fullname'] + '<br>';
      if(user_index >= 5){
        if(parseInt(user_index) === 5){
            user_list += 'and '+ (users.length - 5) +' others'; 
          }
        return false;
       }
    });
  return '<a href="#!" class="endorse-user" data-toggle="modal" data-target="#endoresedpopup"><span class="visible_tooltip endorse-user'+ users[0]['post_id'] +' " data-trigger="hover" type="button" data-toggle="popover" data-placement="top" data-html="true" title="" data-content="'+ user_list + '" data-original-title=""><span id="example_popover" class="other-endorse endorsed_popup" endors-poup-post="'+ users[0]['post_id'] +'" space-id="'+current_space_id+'">' + (users.length) + ' others</span></span></a>';
}

function getFeed(){
  category = $('.categories-wrap').find('.category-selected').attr('key');
  category = category?category:'';
  offset = $('.post-block').length;
  if(!post_null) {
  feed_xhr = $.ajax({
    type: 'GET',
    dataType: 'JSON',
    url: baseurl+'/posts?space_id='+session_space_id+'&offset='+offset+'&category='+category,
    beforeSend: function(){
      $('.show_puff').show();
    },
    success: function(response){
      var parser = new DOMParser;
      var source = $("#post_template").html();
      var template = Handlebars.compile(source);
      var html=''; 
      $.each(response.posts, function(index, post){
        post_category = response.space_category;
        post['space_category'] = post_category;
        var post_subject = parser.parseFromString(
                  '<!doctype html><body>' + post.post_subject,
                  'text/html');
        post.post_subject = post_subject.body.textContent;
        post['logged_user'] = response.user;
        post['baseurl'] = baseurl;
        post['is_logged_in_user_admin'] = is_logged_in_user_admin;
        post['feature_restriction'] = response.feature_restriction;
        html += template(post);
      });
      $('#load_more_post').before(html);
      $('#load_more_post').show();
      $('[data-toggle="popover"]').popover();
      $(".show_extra_content").parent().attr("href","javascript:void();");
      onYouTubeIframeAPIReady();
      videoPlayLog();
      if(response.posts.length == 0) {
          post_null = true;
      }
    },complete:function(){
      $('.show_puff').hide();
      $('.lazy-loading.post').hide();
      feed_xhr = null;
      sticky_sidebar_stop = false;
      if($(window).width() > 767 && $('.feed-col-right .post-wrap').length > 3) {
          $('#left-content, .right-content').theiaStickySidebar({
              additionalMarginTop: 68
          });
      }
      hidePinPostOption();
    }
  });
  }
}

function getPost(post_id, filters){
  $.ajax({
    type: 'GET',
    dataType: 'JSON',
    data:filters,
    url: baseurl+'/post/'+post_id+'?space_id='+session_space_id,
    success: function(response) {
      getpostContent(post_id,response);
      videoPlayLog();
    },complete:function(){
      feed_xhr = null;
      hidePinPostOption();
    }
  });
}

function hidePinPostOption() {
  var unpin_post = $('.unpin_post').length;
  if(unpin_post == 4) {
    $('.pin_post').remove();
  }
}

function getpostContent(post_id, data){
  var source = $("#post_template").html();
  var template = Handlebars.compile(source);
  var html='';
  $.each(data.posts, function(index, post){
    post['space_category'] = post_category;
    post['logged_user'] = data.user;
    post['baseurl'] = baseurl;
    post['is_logged_in_user_admin'] = is_logged_in_user_admin;
    post['feature_restriction'] = data.feature_restriction;
    html += template(post);
  });
  $('.post-block.'+post_id).replaceWith(html);
  $('[data-toggle="popover"]').popover();
}

function getEndorseContent(post_id, data){ 
      var source = $("#post_template").html();
      var source = $(source).find('.like-detail-section').html();
      var template = Handlebars.compile(source);
      var html=''; 
      $.each(data.posts, function(index, post){ 
        post['baseurl'] = baseurl;
        html += template(post);
      });
      $('.post-block.'+post_id+' .like-detail-section').html(html);
      $('[data-toggle="popover"]').popover();
}

function refreshPost(post_id, filters){
  return getPost(post_id, filters);
}

function refreshFeed() {
  $('.post-block').remove();
  $('.lazy-loading.post').show();
  getFeed();
}

function getExtention(url){
  ext = url.split('.');
  return ext.pop();
}

function feedRequest(){
  category = post_feed_data['category']?'&tokencategory='+post_feed_data['category']:'';
  if (feed_xhr) {
    if (category) {
      feed_xhr.abort();
      feed_xhr = null;
    } else {
        return;
    }
  }
  if(post_feed_data['is_scroll'] === 0) {
    $('.feed-col-right .post-wrap').remove();
  }
  feed_xhr = $.ajax({
    type: "GET",
    dataType: "html",
    url: baseurl+'/get_ajax_posts/'+post_feed_data['space_id']+'?limit='+post_feed_data['offset']+category,
    beforeSend:function(){
      $('.show_puff').show();
    },
    success: function(response) { 
      $(window).scrollTop($(window).scrollTop()-2);
      if(response === ""){
        $('#load_more_post').hide();
        $('.show_puff').hide();
        content_found = false;
        $('#load_more_post').before('<div class="post-wrap"><div class="post"><div class="no-result-div"><div class="no-result-col"><i class="fa fa-search" aria-hidden="true"></i><p>No result found.</p></div></div></div></div>');
      }else{
        content_found = true;
        $('#load_more_post').show();
      }
      
      $('#load_more_post').before(response);
      $('.load_ajax_new_posts').val(0);
      $('.post_show_hidden').val(3);
      post_feed_data['offset'] += 3;

      video_player_log_bind();
      onYouTubeIframeAPIReady();
      postsLoadedSuccessfully();
      $('[data-toggle="popover"]').popover();
    },
    error: function(xhr, status, error) {
      $('.load_ajax_new_posts').val(0);
      $('.show_puff').hide();
    },
    complete:function(){
      $('.show_puff').hide();
      $('.lazy-loading.post').hide();
      feed_xhr = null;
      hidePinPostOption();
    }
  });
}


function getTwitterFeeds() {
  $.ajax({
    type: 'get',
    url: baseurl +"/get_twitter_feeds?space_id="+current_space_id,
    success: function (response) {
      if(response)
        $('.twitter-feed-section-dashboard').html(response);

      return false;
    },
    error:function(xhr, status, error) {
      logErrorOnPage(xhr, status, error, 'getTwitterFeeds');
    }
  });
}

function getTopPost(){
    var date = new Date();
    $.ajax({
      type: "GET",
      url: baseurl+'/gettopthreepost?month='+ (date.getMonth()+1) +'&year='+ date.getFullYear()+'&company=&space_id='+current_space_id,
      success: function( response ) {
        $('.top-post-ajax-div').html(response);
        $('.top-post-front, .lazy-loading.top-post').toggleClass('hidden');
      }
    });
}
function changeUrl(page, url) {
    if (typeof (history.pushState) != "undefined") {
      var obj = {Page: page, Url: url};
      single_post_view = null;  
      history.pushState(obj, obj.Page, obj.Url);
    } else {
      console.log("This browser does not support HTML5.");
    }
}

window.addEventListener('popstate', function() {
  if(single_post_id && (single_post_id !== '0')) {
    getSinglePost(single_post_id);
  }
});

function expandPost(postid,single) {
    var post_selector = $('#post_' + postid+' .post-feed-section').closest('.post-wrap');
    post_selector.removeClass('minimize');
    if(single == 'single'){
       $('#post_' + postid).removeClass('minimize');
    }
    post_selector.find('.m-collapse').addClass('minimize-post');
    post_selector.find('.m-collapse').removeClass('m-collapse');
    if(single === undefined){ 
      post_selector.find('.post-description .full_description').hide();
      post_selector.find('.post-description .trim_description').show();
      post_selector.find('.post-description .trim_description').removeClass('hidden');
    }
    post_selector.find('.expand_view_content').show();
    post_selector.find('.minimize-post').html('<span class="dropdown-post-icon"><img src="'+baseurl+'/images/ic_unfold_less.svg"></span>' + 'Minimise post');
}

function getSinglePost(post_id) {
  $.ajax({
    type: "GET",
    url: baseurl+'/post/'+ post_id + '?space_id='+session_space_id,
    success: function( response ) {
      single_post_data = response;
      var source = $("#post_template").html();
      var template = Handlebars.compile(source);
      var html='';
      var post_id = 0;
      $.each(response.posts, function(index, post){
        post_id = post.id;
        post_category = response.space_category;
        post['space_category'] = post_category;
        post['logged_user'] = response.user;
        post['baseurl'] = baseurl;
        post['is_logged_in_user_admin'] = is_logged_in_user_admin;
        post['single_post_view'] = single_post_view;
        post['feature_restriction'] = response.feature_restriction;
        html += template(post);
      });
      $('.single_post_content').html(html);
      $('#single_post_modal').modal();
      $('.modal-backdrop').eq(0).addClass('second-overlay');
      $('.single-post-popup .post-feed-section > iframe').remove();
      var collapsed_view = $('.single_post_content .minimize-collapse,.m-collapse');
      if(collapsed_view.length > 0) {
        expandPost(post_id,'single');
      }
      videoPlayLog();
    }
  });
}

$(document).on('hidden.bs.modal', '#single_post_modal', function () { 
    changeUrl('Client Share', baseurl+'/clientshare/'+current_space_id);
    single_post_id = null;
});

$(document).on('hidden.bs.modal', '.modal', function () {
    if(single_post_id != 0 && single_post_id){
       $('body').addClass('modal-open');
    }
});


/* Initial scripts */
getTopPost();

$(document).on('click', 'img.endorse', function () {
  feedEndorse($(this).attr('id'), 1);
});

$(document).on('click', 'img.dendorse', function () {
  feedEndorse($(this).attr('id'), 0);
});

function feedEndorse(post_id, endorse){
  $.ajax({
    type: 'POST',
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    data: {post_id:post_id, endorse:endorse},
    url: baseurl+'/endorsePost',
    success: function (response) {
      getEndorseContent(post_id, response);
    }
  });
}

if(!window.location.pathname.includes(path_name))
  window.history.pushState(null, null, '/clientshare/'+current_space_id);

if(single_post_view) {
  $(document).on('shown.bs.modal', '#single_post_modal', function () {
    $(document).find('body').addClass('single-post-body')
  });

  $(document).on('hidden.bs.modal', '#single_post_modal',  function () {
    $(document).find('body').removeClass('single-post-body')
  });

  getSinglePost(single_post_id);

  customLogger({
      'space_id':session_space_id,
      'action': 'View single post',
      'content_type': 'AppPostMedia',
      'content_id': single_post_id,
      'metadata': {'post_id':single_post_id}
    }, true);
}

function linkify(input_text) {
    var replaced_text, replace_pattern_one, replace_pattern_two, replace_pattern_three;
    var link_css_class = 'linkify-anchor';

    replace_pattern_one = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
    replaced_text = input_text.replace(replace_pattern_one, '<a href="$1" class='+link_css_class+' target="_blank">$1</a>');

    replace_pattern_two = /(^|[^\/])(www\.[\S]+(\b|$))/ig;
    replaced_text = replaced_text.replace(replace_pattern_two, '$1<a class='+link_css_class+' href="http://$2" target="_blank">$2</a>');

    replace_pattern_three = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/ig;
    replaced_text = replaced_text.replace(replace_pattern_three, '<a class='+link_css_class+' href="mailto:$1">$1</a>');

    return replaced_text;
}

function htmlSubstring(string, limit) {
    var expression, validate = /<([^>\s]*)[^>]*>/g,
        stack = [],
        last = 0,
        result = '';

    while ((expression = validate.exec(string)) && limit) {
        var temp = string.substring(last, expression.index).substr(0, limit);
        result += temp;
        limit -= temp.length;
        last = validate.lastIndex;

        if (limit) {
            result += expression[0];
            if (expression[1].indexOf('/') === 0) {
                stack.pop();
            } else if (expression[1].lastIndexOf('/') !== expression[1].length - 1) {
                stack.push(expression[1]);
            }
        }
    }
    
    result += string.substr(last, limit);
    return result.trim();
}

function copyTextToClipboard(text) {
    var textArea = document.createElement("textarea");
    textArea.style.position = 'fixed';
    textArea.style.top = 0;
    textArea.style.left = 0;
    textArea.style.width = '2em';
    textArea.style.height = '2em';
    textArea.style.padding = 0;
    textArea.style.border = 'none';
    textArea.style.outline = 'none';
    textArea.style.boxShadow = 'none';
    textArea.style.background = 'transparent';
    textArea.value = text;
    
    if(single_post_view)
      $('#single_post_modal').append(textArea);
    else
      document.body.appendChild(textArea);

    textArea.focus();
    textArea.select();

    try {
        document.execCommand('copy');
    } catch (err) {
        alert('Could not copy the URL - please update your browser to the latest version.');
    }

    if(single_post_view)
      $('#single_post_modal').find(textArea).remove();
    else
      document.body.removeChild(textArea);
}

function iosCopyToClipboard(val) {
    el = document.getElementById('copy_post_link_ios');
    el.innerHTML = val;
    var range = document.createRange();
    range.selectNodeContents(el);
    var s = window.getSelection();
    s.removeAllRanges();
    s.addRange(range);
    el.setSelectionRange(0, 999999); // A big number, to cover anything that could be inside the element.
    document.execCommand('copy');
}

$(document).on('click', '.copy-post-link', function (e) {
    e.preventDefault();
    if (navigator.userAgent.match(/ipad|ipod|iphone/i)) {
        iosCopyToClipboard($(this).attr('data-href'));
    } else {
    copyTextToClipboard($(this).attr('data-href'));
    }
    $(this).closest('.dropdown-toggle').dropdown('toggle');
    return;
});

$('.modal').on('shown.bs.modal', function () {
  $(window).trigger('resize');
});

$(document).on('click', '.endorse-user [data-toggle="popover"]', function(){
  $(this).popover('hide');
});

$(document).on('click', '.post-header-col [data-toggle="popover"]', function(){
  $(this).popover('hide');
});

function pasteAsPlainText(elem, e) {
  e.preventDefault();
  var text = '';
  if (e.clipboardData || e.originalEvent.clipboardData) {
    text = (e.originalEvent || e).clipboardData.getData('text/plain');
  } else if (window.clipboardData) {
    text = window.clipboardData.getData('Text');
  }

  if (document.queryCommandSupported('insertText')) {
    document.execCommand('insertText', false, text);
  } else {
    document.execCommand('paste', false, text);
  }
}


function checkBlockWords(subject, body, CSRF_TOKEN){
  var any_error=false;
  $.ajax({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      type: 'POST',
      url: baseurl + '/matchwordsubject',
      data: {
          subject: subject,
          body: body,
          _token: CSRF_TOKEN
      },
      dataType: 'json',
      async: false,
      success: function (response) {
          if (response != '') {
              if (response.subject) {
                  var block_word_subject = response.subject.toString().replace(/\,/g, '", "');
                  if (block_word_subject != '') {
                    $('.post_subject:visible').addClass('word-block-error');
                    $('.post_subject:visible').parent().find('.error-msg').remove();

                    var message_html = '<span class="error-msg error-body text-left" style="text-align: left;">';
                    message_html += 'This post contains the following blocked word(s): "' + block_word_subject + '"</br>';
                    message_html += 'Please remove any blocked words before adding your post</span>';

                    $('.post_subject:visible').after(message_html);
                    any_error = 1;
                  }
              }
              if (response.body1) {
                  var block_word_body = response.body1.toString().replace(/\,/g, '", "');
                  if (block_word_body != '') {
                      $('.main_post_ta:visible').addClass('word-block-error');
                      $('.main_post_ta:visible').parent().find('.error-msg').remove();

                      var message_html = '<span class="error-msg error-body text-left" style="text-align: left;">';
                      message_html += 'This post contains the following blocked word(s): "' + block_word_body + '"</br>';
                      message_html += 'Please remove any blocked words before adding your post</span>';
                      
                      $('.main_post_ta:visible').after(message_html);
                      any_error = 1;
                  }
              }
          } else {
              if (!subject || !subject.length > 0) {
                  $('.post_subject:visible').parent().find('.error-msg').remove();
                  $('.post_subject:visible').after('<span class="error-msg error-body text-left" style="text-align: left;">Subject is mandatory</span>');
                  any_error = 1;
              }
              if (!body || !body.length > 0) {
                  $('.post-description-textarea:visible').parent().find('.error-msg').remove();
                  $('.post-description-textarea:visible').after('<span class="error-msg error-body text-left" style="text-align: left;">Body is mandatory</span>');
                  any_error = 1;
              }
          }
      },
      error: function (error) {
          ev.preventDefault();
      }
    });

  return any_error;
}

function addPost(form_data){
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    type:'POST',
    url:baseurl+'/addpost',
    data:form_data,
    dataType:'JSON',
    success: function(response){
      if(typeof response.message != 'undefined' && response.message == 'user_deleted') {
           window.location.href = baseurl+'/logout';  
      }
      post_null = false;
      refreshFeed();
      resetAddPostFormElements();
      addProgressBarsection('add_post');
    },
    error: function(xhr, status, error) {
      errorOnPage(xhr, status, error);
    }
  });
}

$(document).on('click', '#save_post_btn_new', function (ev) {
  ev.preventDefault();
  $('.add_post_form').find('.form-submit-loader').show();
  var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
  var subject = $('.post_subject:visible').val();
  var body = $('.main_post_ta:visible').val();
  blocked_word = checkBlockWords(subject, body, CSRF_TOKEN); 
  if(blocked_word) {
    $('.add_post_form').find('.form-submit-loader').hide();
  }
  if(!blocked_word) {
    $('.add_post_form').find('.form-submit-loader').show();
    setTimeout(triggerAddPost, 500);
  }
});

function triggerAddPost(){
  addPost($('#save_post_btn_new').closest('form').serialize());
}


function resetAddPostFormElements(){
  $('#discard').trigger('click');
  $('.add_post_form').find('.form-submit-loader').hide();

  $('.alert-checkbox').parent().addClass('active').removeClass('disable_check');
  $('.select_all_alert').parent().addClass('active').removeClass('disable_check');
  $('.selection_alert').text('Everyone');

  $('.visiblity-checkbox').parent().addClass('active');
  $('.select_all_visibility').parent().addClass('active');
  $('.selection_visibility').text('Everyone');
  $('form.add_post_form textarea').height(29);
  loadEditPostTemplate();
}


function singlePostEditTemplate() {
  $.ajax({
    type: 'GET',
    dataType: 'html',
    url: baseurl+'/single_post_edit_template/'+session_space_id,
    success: function(response) {
      singlePostEditTemplateInit(response);      
    }
  });
}

function singlePostEditTemplateInit(response) {
  $('.container-prime').html(response);
}

function resetEditedSinglePost() {
  $('.single-post-edit-view').remove();
  $('.single_post_content').show();
  $('.edit_post_aws_files_data').val('');
  $('.edit_media_div').html();
}

function submitEditedPost(form) {
  post_data = form.serializeArray();
  post_id = form.find('[name="post[id]"]').val();

  $.ajax({
    type: 'POST',
    dataType: 'json',
    data: post_data,
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    url: baseurl+'/updatepost/',
    success: function(response) {
      getSinglePost(post_id);
      resetEditedSinglePost();
    }
  });
}

var edit_post_files = new Array();

function uploadEditPostFileCompleted(file_data) {
  
  html = '<div class="upload-content post_categories edit_media_div_container" id="'+file_data.uid+'">';
  html+= ' <span class="close">';
  html+= ' <img src="'+baseurl+'/images/ic_deleteBlue.svg" id="" class="edit_file_del" fileid="'+file_data.originalName+'" onclick="removeUploadedFile(this, \'edit_post_files\', \''+uid+'\')"></span>';
  html+= ' <a class="findmedia attach-link full-width" href="javascript:void(0)" onclick=""><img class="" src="'+baseurl+'/images/ic_IMAGE.svg" media-id="'+file_data.uid+'">';
  html+= ' <span class="attachment-text">'+file_data.originalName+'</span>';
  html+= ' </a></div>';

  $('.upload-preview-wrap').append(html);
  $('.' + file_data.uid).remove();
  edit_post_files = new Array();
}

function uploadEditPostFileError(file_data) {}

function removeUploadedFile(element, storage, uid) {
  if ($(element).attr('id') == "")
      $(element).attr('id', 0);
  $('#'+uid+'.edit_media_div_container').remove();

  file_object = new Array();
  $.each(window[storage], function(index, file) {
    if(file.uid != uid) {
      file_object.push(file);
    }
  });
  window[storage] = file_object;
}

function uploadEditPostFile() {
  direct_upload_s3_data.push({
    'storage': 'edit_post_files',
    'progress_element_class': 's3_progress',
    'form_field_class': 'edit_post_aws_files_data',
    'done_callback': 'uploadEditPostFileCompleted',
    'error_callback': 'uploadEditPostFileError',
    'allowed_extension': ['pdf', 'docx', 'ppt', 'pptx', 'mp4', 'doc', 'xls', 'xlsx', 'csv' , 'mov', 'MOV', 'png', 'jpeg', 'jpg'],
    'progress_bar_ele': '.upload-preview-wrap'
  });

  $('#upload_s3_file').trigger('click');
}

$(document).on('click', '.edit-single-post-data', function() {
  single_post_clone = $('.container-prime').clone();
  single_post_clone.find('.upload-preview-wrap').html('');

  var multiselect_attributes = {
    numberDisplayed: 1,
    includeSelectAllOption: true,
    enableCaseInsensitiveFiltering: true,
    buttonWidth: '100%',
    nonSelectedText: 'NOTHING SELECTED',
    disabledText: 'Disabled'
  };
  

  single_post_clone.find('select')
    .not('.single-post-edit-visiblity-select')
    .multiselect(multiselect_attributes);

  single_post_clone.show();
  single_post_clone.addClass('single-post-edit-view');
  $('.single_post_content').hide();
  var regex = /<br\s*[\/]?>/gi;
  single_post_clone.find('textarea.post-subject-textarea').val(single_post_data.posts[0].post_subject.replace(regex, "\n"));
  single_post_clone.find('textarea.post-description-textarea').val(single_post_data.posts[0].post_description.replace(regex, "\n"));
  
  single_post_clone.find('.single-post-edit-category-select').multiselect('select', [single_post_data.posts[0].meta_array.category]);
  
  single_post_clone.find('.single-post-edit-visiblity-select').multiselect({
    injectElement: function(element) {
      return '<span class="community-member-company">'+$(element).attr('data-company')+'</span>';
    },
    numberDisplayed: 1,
    includeSelectAllOption: true,
    enableCaseInsensitiveFiltering: true,
    buttonWidth: '100%',
    nonSelectedText: 'NOTHING SELECTED',
    disabledText: 'Disabled'
  });
  
  single_post_clone.find('.single-post-edit-visiblity-select').multiselect('select', single_post_data.posts[0].visibility.split(','));

  single_post_clone.find('.single-post-edit-alert-select').multiselect('selectAll', false);
  single_post_clone.find('.single-post-edit-alert-select').multiselect('disable');
  single_post_clone.find('.single-post-edit-alert-select').multiselect('updateButtonText');

  single_post_clone.find('input[name="post[id]"]').val(single_post_data.posts[0].id);
  single_post_clone.find('input[name="space[id]"]').val(single_post_data.posts[0].space_id);

  single_post_clone.find('meta[name="csrf-token"]').attr('content', $('meta[name="csrf-token"]').attr('content'));
  
  html='';
  $.each(single_post_data.posts[0].post_media, function(index, file) {
    html+= '<div class="upload-content post_categories edit_media_div_container" style="">';
    html+= '<span class="close"><img src="'+baseurl+'/images/ic_deleteBlue.svg" id="" class="edit_file_del" fileid="'+file.id+'" onclick="close_preview_edit(this)"></span>';
    html+= '<a class="findmedia attach-link full-width" href="javascript:void(0)" onclick="">';
    html+= '<img class="" src="'+baseurl+'/images/ic_IMAGE.svg" media-id="'+file.id+'" viewfile="">';
    html+= '<span class="attachment-text">'+file.metadata.originalName+'</span>';
    html+= '</a></div>';
  });


  single_post_clone.find('.edit_media_div').html(html);
  
  $('.single_post_content').after(single_post_clone);
  $('#single_post_modal .single-post-repost').attr('id','single-post-repost-feed');
  $('#single_post_modal .single-edit-post-checkbox').attr('for','single-post-repost-feed')
  autosize(document.querySelectorAll('textarea.t1-resize'));
  autosize(document.querySelectorAll('textarea.t2-resize'));
});

$(document).on('click', '.single-post-repost', function() {
  if($(this).prop('checked')) {
    $(this).closest('.single-post-edit-view').find('.single-post-edit-alert-select').multiselect('enable');
  } else {
    $(this).closest('.single-post-edit-view').find('.single-post-edit-alert-select').multiselect('disable');    
  }
  $(this).closest('.single-post-edit-view').find('.single-post-edit-alert-select').multiselect('selectAll', false);
  $(this).closest('.single-post-edit-view').find('.single-post-edit-alert-select').multiselect('updateButtonText');
});

$(document).on('click', '.submit-edited-post', function() {
  form = $(this).closest('form');
  form.find('.form-submit-loader').show();
  var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
  var subject = form.find('textarea.post-subject-textarea').val().trim();
  var body = form.find('textarea.post-description-textarea').val().trim();
  blocked_word = checkBlockWords(subject, body, CSRF_TOKEN);
  
  if(!blocked_word) {
    $(this).closest('form').submit();
  } else {
    form.find('.form-submit-loader').hide(); 
  }
});

$(document).ready(function(){
  singlePostEditTemplate();
});