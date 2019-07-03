function mixpanelLogger(data,async) {
  $.ajax({
    type: "POST",
    async : async,
    url: baseurl+'/mixPannelInitial',
    data: data
  });
}

function customLogger(data,async) {
  $.ajax({
    type: "POST",
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    async : async,
    url: baseurl+'/custom_logger',
    data: data
  });
}

function videoPlayLog() {
    $('video').bind('play', function (e) {
        customLogger({
            'space_id':session_space_id,
            'action': 'play video',
            'content_type': 'AppPostMedia',
            'metadata': {'url':$(this).attr('href')},
            'content_id': $(this).attr('media-id')
        }, true);
    });
}

$(document).ready(function() {

	$(document).on('click', '.user-comment-description .linkify-anchor', function(){
		customLogger({
			'space_id':session_space_id,
			'action': 'View comment link',
			'content_type': 'AppPostMedia',
			'metadata': {'url':$(this).attr('href')}
		}, true);
	});

	$(document).on('click', '.findmedia', function(){

		if($(this).find('video').length) return;
		
		customLogger({
			'space_id':session_space_id,
			'action': 'View Attachment',
			'content_type': 'AppPostMedia',
			'content_id' : $(this).find('img').attr('media-id')
		}, true);
	});

	$(document).on('click', '.file-download', function(){
		customLogger({
			'space_id':session_space_id,
			'action': 'download post attachment',
			'content_type': 'AppPostMedia',
			'content_id' : $(this).attr('media-id'),
			'metadata': {'url':$(this).attr('href')}
		}, true);
	});

	$(document).on('click', '.link-view-section a, .trim_description a', function() {
		customLogger({
			'space_id':session_space_id,
			'action': 'view embedded url',
			'description': $(this).attr('href')
		}, true);
	});
	
	$('ul.categories li').on('click', function(){ 
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Filter feed by category',
			'metadata' : {'category': $(this).find('.chip span').html()}
		}, true)
	});

	$('li.top_post_seller').on('click', function(){ 
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Filter top post Seller'
		}, true)
	});

	$('li.top_post_buyer').on('click', function(){ 
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Filter top post Buyer'
		}, true)
	});

	$('.last-month, .next-month').on('click', function(){ 
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Change month top posts'
		}, true)
	});

	$('.executive-file').on('click', function(){ 
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Open exec summary file'
		}, true)
	});

	$('ul.company-dropdown li').on('click', function(){ 
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Switch share'
		}, false)
	});

	$('.findmedia ').on('click', function(){ 
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Open post attachment'
		}, true)
	});

	$('a.endrose ').on('click', function(){ 
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Like post'
		}, true)
	});

	$('a.s-everyone').on('click', function(){ 
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Change post visibility'
		}, true)
	});

	$(document).on('click', 'a.edit-post-cog', function () {
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Edit post'
		}, true)
	});

	$(document).on('click', 'a.minimize-post', function () {
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Minimize post'
		}, true)
	});


	$('a.profile_popup').on('click', function(){
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Open profile'
		}, true)
	});

	$('a.ic_search').on('click', function(){
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Search'
		}, true)
	});

	$(document).on('click', 'ul.search-dropdown li', function () {
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Search result'
		}, false)
	});

	$('a.remove_badge').on('click', function(){
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Check Notifications'
		}, true)
	});

	$(document).on('click', 'ul.notificationdropdwon li', function () {
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Open Notification'
		}, false)
	});

	$('li.domain_inp_delete').on('click', function(){
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Delete domain'
		}, true)
	});

	$('li.domain_inp_edit').on('click', function(){
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Edit domain'
		}, true)
	});

	$('div.pending-eye-wrap ').on('click', function(){
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'View invite history'
		}, true)
	});

	$('.download_feedback_pdf').on('click', function(){
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Download feedback'
		}, false)
	});

	$('.year_filter, .month_filter').on('change', function(){
		mixpanelLogger({
			'space_id': session_space_id,
			'event_tag':'Change analytics year/month'
		}, true)
	});

	$('.share_main_cb').on('change', function(){
		mixpanelLogger({
			'space_id': session_space_id,
			'event_tag':'Select share analytics'
		}, true)
	});

	$('.top-post-ajax-div div.box').on('click', function(){
		mixpanelLogger({
			'space_id': session_space_id,
			'event_tag':'Open top post'
		}, true)
	});

	$('a.single-share-report').on('click', function(){
		mixpanelLogger({
			'space_id': session_space_id,
			'event_tag':'Download individual analytics'
		}, true)
	});

	$('input.share_buyer').on('change', function(){
		mixpanelLogger({
			'space_id': session_space_id,
			'event_tag':'Select buyer analytics'
		}, true)
	});

	$('input.share_seller').on('change', function(){
		mixpanelLogger({
			'space_id': session_space_id,
			'event_tag':'Select seller analytics'
		}, true)
	});

	$('.feed-button').on('click', function(){
		mixpanelLogger({
			'space_id': session_space_id, 
			'event_tag':'Feed'
		}, false)
	});
	
});