var comment_post_id;

function pullPostId(post_id){
	post_id = $('div.comment-area:not(.edit-comment-area)').attr('id');
	return post_id.replace('comment_input_area', '');
}

function mentionsComment(element, post_id){
	$(element).mentionsInput({
        source:baseurl+'/mention_user?post_id='+post_id,
        showAtCaret: true
    });
	$(element).mentionsInput('editReady', element);
}

$(document).on('click', 'div.comment-area:not(.edit-comment-area)', function(){
    if(comment_post_id == $(this).attr('data-postid')) return;
    comment_post_id = $(this).attr('data-postid');
	mentionsComment($(this), comment_post_id);
});