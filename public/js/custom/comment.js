var edit_comment_triggered = false;

function update_comment(element, comment_id) {
    var edit_comment_element = $(element).closest('.comment-edit-section').find('.edit-comment-area');
    var new_value = edit_comment_element.html();
    var uploading_file = $(element).closest('.post-block').find('.s3_running_process').length;
    if(!(edit_comment_element.text().trim().length) || !placeholderCheck(edit_comment_element) || uploading_file){
        edit_comment_element.focus();
        return false;
    }
    $.ajax({
        type: 'POST',
        async:false,
        dataType: 'JSON',
        url: baseurl + '/comments/'+comment_id,
        data: {
            _method: 'PATCH',
            _token: $('meta[name="csrf-token"]').attr('content'),
            'comment': {'comment_text':new_value, 'comment_attachment':window['attachment_'+comment_id]}
        },
        success: function (response) {
            var post_id = $(element).closest('.post-block').find('input[name="post_id"]').attr('id');
            refreshPost(post_id, {'comment_limit':0});  
            edit_comment_triggered = false;
        },
        error: function (xhr, status, error) {
            errorOnPage(xhr, status, error);    
        },
        complete: function(){
            window['attachment_'+comment_id] = new Array();
        }
    });
}

function getComment(comment_id){
    var comment = 'Please wait..';
    $.ajax({
        type: 'GET',
        dataType: 'json',
        async : false,
        url: baseurl + '/comments/'+comment_id,
        success: function (response) {
            comment = response;
        },
        error: function (xhr, status, error) {
            errorOnPage(xhr, status, error);
        }
    });
    return comment;
}

function delete_comment_confirm(element) {
    var postid = $(element).attr('id');
    var commentid = $(element).attr('commentid');
    var userid = '';
    var spaceid = $(element).attr('spaceid');
    var commentlimit = '';
    var comment = '';
    var morecheck = $('.checkclickedviewmore' + postid).val();
    var view_more = 'false';
    $.ajax({
        type: "GET",
        url: baseurl + '/add_comments?postid=' + postid + '&userid=' + userid + '&comment=' + comment + '&commentlimit=' + commentlimit + '&morecheck=' + morecheck + '&view_more=' + view_more + '&spaceid=' + spaceid + '&commentid=' + commentid + '&action=delete',
        success: function (response) {
            $('.comments' + postid).html(response);
            $('#comment_input_area' + postid).val('');
            $("#delete_modal_comment").modal('hide');
            refreshPost(postid, {'comment_limit':0});
        },
        error: function (xhr, status, error) {
            logErrorOnPage(xhr, status, error, 'delete_comment_confirm');
        }
    });
}

function delete_comment(post_id, comment_id, space_id) {
    $('.del_comment').attr('id', post_id);
    $('.del_comment').attr('commentid', comment_id);
    $('.del_comment').attr('spaceid', space_id);
    $("#delete_modal_comment").modal('show');
}

function commentPreview(file_data, element){
    if(!file_data.attachments.length) $('.feed-post-attachment-box.'+file_data.id).parent().hide();
    var source = $("#comment_attachment_preview").html();
    var template = Handlebars.compile(source);
    window['attachment_'+file_data.id] = new Array();
    $.each(file_data.attachments, function(key){
        window['attachment_'+file_data.id].push(this.metadata);
    });

    var html = template({'comment_files':window['attachment_'+file_data.id], 'uid': file_data.id});
    $('.feed-post-attachment-box.'+file_data.id).html(html);
}

function edit_comment(comment_id, element) {
    var post_id = $(element).closest('.post-block').find('input[name="post_id"]').attr('id');
    comment_data = getComment(comment_id);
    comment = comment_data.comment;

    div = '<div class="comment-edit-section"><div contenteditable="true" class="form-control no-border edit-comment-area comment-area" id="'+comment_id+'" data-placeholder="Write a comment..." areaid="'+comment_id+'" style="border: 2px solid red; min-height:30px;width:200px">'+comment+'</div><div class="comment-attach-col"><input type="submit" value="File Attachment" class="comment_attachment comment_edit_attachment_trigger" data-commentid="'+comment_id+'" data-postid="'+post_id+'" style="float:right;"></div><button type="button" class="invite-btn right save_comment" onclick="return update_comment(this,\'' + comment_id + '\')">Save</button><div class="attachment-box-row full-width"><div class="feed-post-attachment-box '+comment_id+'"></div></div><div class="comment_edit_attachment_progress full-width '+comment_id+'"></div></div>';

    comment_wrap = $(element).closest('.user-comment-post');
    comment_wrap.find('.user-comment-detail').hide();
    comment_wrap.append(div);

    if($(element).attr('data-comment-restriction'))
        $('.comment_attachment').remove();

    mentionsComment(comment_wrap.find('.edit-comment-area'), post_id);
    commentPreview(comment_data, element);
    comment_wrap.find('.edit-comment-area').focusEnd();
    edit_comment_triggered = true;
}

function addComment(comment_data, element){
    $.ajax({
        type: 'POST',
        dataType: 'html',
        data: {
            'post_id': comment_data['postid'] ,
            'user_id': comment_data['userid'] ,
            'space_id': comment_data['spaceid'],
            'comment': comment_data['comment'],
            'commentlimit': comment_data['commentlimit'] ,
            'morecheck': comment_data['morecheck'] ,
            'view_more': 0,
            'attachments': comment_data['attachments'],
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function(){
            $(element).after('<i class="fa fa-circle-o-notch fa-spin"></i>');
            $(element).attr('disabled', true);
            $(element).val('');
        },
        url: baseurl + '/comments',
        success: function (response) {
            comment_post_id = null;
        },
        error: function (xhr, status, error) {
            logErrorOnPage(xhr, status, error, 'addComment');
        },
        complete: function(){
            $(element).parent().find('.fa-circle-o-notch').remove();
            $(element).attr('disabled', false);
            $(element).val('SEND');
        }
    });
}

function showComments(comment_data, comments){
    $('#post_'+comment_data['post_id']).find('.comment-wrap').html(comments)
    $('#comment_input_area' + comment_data['post_id']).val('');
    $("#delete_modal_comment").modal('hide');
    comment_post_id = null;
}


$(document).on("mouseup", function(e){
    var container = $('.comment-edit-section');
    var modal_container = $('.swal2-modal');
    if(container.has(e.target).length === 0 && edit_comment_triggered){
        if(modal_container.has(e.target).length) return true;
        swal({
            html: $('#discardModalcomment .modal-content').html(),
            customClass: 'simple-alert discard-comment',
            showConfirmButton: false,
            animation: false
        });
        $('.discard_comment').attr('id', $('div.edit-comment-area').attr('id'));
        return false;
    }
});

$(document).on('click', '.comment-show-less', function(){
    if($(this).hasClass('show-more')){
        $(this).parent().find('.post-comment').css('max-height', '100%');
        $(this).html('Show Less');
        $(this).toggleClass('show-more', 'show-less');
    } else {
        $(this).parent().find('.post-comment').css('max-height', '50px');
        $(this).html('Show More');
        $(this).toggleClass('show-more', 'show-less');
    }
});

function placeholderCheck(element){
    if(navigator.userAgent.match(/msie/i) || navigator.userAgent.match(/trident/i) ) {
        var placeholder_content = 'Add a comment or tag someone using @...';
        return placeholder_content == $(element).text() ? false : true;
    }
    return true;
}

$(document).on('click', '.send_comment', function (e) {
    var tag = $(this);
    var post_id = $(this).attr('datapostid');
    var user_id = $(this).attr('datauserid');
    var comment_limit = $(this).attr('commentlimit');
    var space_id = $(this).attr('spaceid');
    var comment = $(this).parent().find('.comment-area').html();
    var more_check = 'false';
    var view_more = 'false';
    var comment_element = $(this).parent().find('.comment-area');
    var uploading_file = $(this).closest('.post-block').find('.s3_running_process').length;

    if(!(comment_element.text().trim().length) || !(placeholderCheck(comment_element)) || uploading_file){
        comment_element.focus();
        return false;
    }

    addComment({
        'postid' : post_id,
        'userid' : user_id,
        'comment' : comment,
        'commentlimit' : comment_limit,
        'morecheck' : more_check,
        'view_more' : view_more,
        'spaceid' : space_id,
        'attachments': window['attachment_'+post_id]

    }, this);
    getPost(post_id,{'comment_limit':2}, 'comment');
    window['attachment_'+post_id] = new Array();
});

$(document).on('click','a.view-more-comments', function(){
    $(this).hide();
    post_id = $(this).closest('.post-block').find('input[name="post_id"]').attr('id');
    $('.'+post_id+'.post-block').find('.user-comment-post.hidden').removeClass('hidden').addClass('user-comment-post-show');
    $('.'+post_id+'.post-block').find('.view-less-comments.hidden').removeClass('hidden');
});

$(document).on('click','a.view-less-comments', function(){
    $(this).addClass('hidden');
    post_id = $(this).closest('.post-block').find('input[name="post_id"]').attr('id');
    $('.'+post_id+'.post-block').find('.user-comment-post-show').addClass('hidden').removeClass('user-comment-post-show');
    $('.'+post_id+'.post-block').find('.view-more-comments').show();
});

function discardComment(){
    $('.user-comment-detail').show();
    $('.comment-edit-section').hide();
    $('#discardModalcomment').modal('hide');
    swal.close();
    edit_comment_triggered = false;
    $('.edit-comment-area').trigger('mouseenter').focusEnd();
}

$(document).on('click', '.discard-comment button', function(){
    swal.close();
    $('.edit-comment-area').trigger('mouseenter').focusEnd();
});

if(navigator.userAgent.match(/msie/i) || navigator.userAgent.match(/trident/i) ) {
    var placeholder_content = 'Add a comment or tag someone using @...';
    $(document).on('focus', '.comment-area', function(){
        if(placeholder_content == $(this).text()) $(this).html('').css('color', 'black');
        $(this).attr('data-placeholder', '');
    });
    $(document).on('blur', '.comment-area', function(){
        if(!$(this).text().trim().length) $(this).html(placeholder_content).css('color', '#b6b6b6');
    });
}

function commentEditAttachment(element){
    direct_upload_s3_data = new Array();
    direct_upload_s3_data.push({
        'storage': 'attachment_'+$(element).attr("data-commentid"),
        'progress_element_class': 's3_progress',
        'form_field_class': 'executive_aws_files_data',
        'done_callback': 'commentEditAttachmentUploaded',
        'error_callback': 'upload_executive_file_error',
        'allowed_extension': constants['POST_EXTENSION'],
        'progress_bar_ele': $(element).closest('.post-block').find('.comment_edit_attachment_progress.'+$(element).attr('data-commentid')),
        'element': element
    });
    $('#upload_s3_file').trigger('click');
}

function commentAttachment(element){
    direct_upload_s3_data = new Array();
    if(!window['attachment_'+$(element).attr("data-postid")]) 
            window['attachment_'+$(element).attr("data-postid")] = new Array();
    direct_upload_s3_data.push({
        'storage': 'attachment_'+$(element).attr("data-postid"),
        'progress_element_class': 's3_progress',
        'form_field_class': 'executive_aws_files_data',
        'done_callback': 'commentAttachmentUploaded',
        'error_callback': 'upload_executive_file_error',
        'allowed_extension': constants['POST_EXTENSION'],
        'progress_bar_ele': $(element).closest('.post-block').find('.comment_attachment_progress.'+$(element).attr('data-postid')),
        'element': element
    });
    $('#upload_s3_file').trigger('click');
}

function commentEditAttachmentUploaded(file_data){
    var send_trigger = direct_upload_s3_data[0]['element'];
    post_id = $(send_trigger).attr('data-postid');
    comment_id = $(send_trigger).attr('data-commentid');
    var source = $("#comment_attachment_preview").html();
    var template = Handlebars.compile(source);
    var html = template({'comment_files':window['attachment_'+comment_id], 'uid': comment_id});

    $(send_trigger).closest('.post-block').find('.comment-edit-section .feed-post-attachment-box.'+comment_id).html(html).parent().show();
    $(send_trigger).closest('.post-block').find('.s3_running_process.'+file_data['uid']).remove();
    $(send_trigger).focus();
}

function commentAttachmentUploaded(file_data){
    var send_trigger = direct_upload_s3_data[0]['element'];
    var post_id = $(send_trigger).attr('data-postid');
    var source = $("#comment_attachment_preview").html();
    var template = Handlebars.compile(source);
    var html = template({'comment_files':window['attachment_'+post_id], 'uid': post_id});

    $(send_trigger).closest('.post-block').find('.feed-post-attachment-box').html(html).parent().show();
    $(send_trigger).closest('.post-block').find('.s3_running_process.'+file_data['uid']).remove();
}

function removeCommentAttachment(element){    
    var uid = $(element).attr('data-uid');
    
    var temp_arr = new Array();
    var attachment = $(element).closest('.comment-attachment');
    var attachment_container = attachment.closest('.attachment-box-row');
    var attachment_id = attachment.attr('id');

    $.each(window['attachment_'+uid], function (key) {
        if (this.uid != attachment_id)
            temp_arr[key] = window['attachment_'+uid][key];
    });
    delete window['attachment_'+uid];
    window['attachment_'+uid] = temp_arr;
    attachment.remove();
    if(!attachment_container.find('.comment-attachment').length){
        window['attachment_'+uid] = new Array();
        attachment_container.hide();
    } 
    return false;
}

$(document).ready(function(){

    $(document).on('click', '.comment-attach-delete', function(){
        removeCommentAttachment(this);
    });

    $(document).on('click', '.comment_attachment_trigger', function(){
        commentAttachment(this);
    });

    $(document).on('click', '.comment_edit_attachment_trigger', function(){
        commentEditAttachment(this);
    });

    $(document).on('keyup', '.comment-area', function(){
        if(!$(this).text().length) {
            $(this).html('');
            $(this).mentionsInput('destroy');
            mentionsComment(this, $(this).attr('data-postId'));
        }
    });
});