<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/


/*
| Following are routes categories which we are using in this project.
| General: Access by all type of users.
| Super-Admin: This category is for routes which are associated with super-admin usertype.
| Admin/User: This category is for routes which are associated with Admin & User usertype.
| Public: Called from outside of the app like "postmark webhook for bounced mail notification",linkedin etc.
*/

use App\Space;
use App\Helpers\{Generic,Logger, Post as PostHelper};
use Illuminate\Http\Request;

/* Public group */
Route::group([], function () {

	Route::get('community/{space_id}', 'ManageShareController@community');

	Route::get('move_company_logo', 'SettingController@moveCompanyLogo');

	Route::get('email_attachment/{link_id}', 'LinkManagerController@emailAttachment');

	Route::get('mi_email_logs', 'ManagementInformationEmailLogController@index');
	Route::any('mi_email_logs/create', 'ManagementInformationEmailLogController@create');
	Route::get('mi_email_logs/show/{space_id}', 'ManagementInformationEmailLogController@show');

	Route::get('u/{id}', 'ManageShareController@shortUrlRedirect');
	Route::get('attachment/{id}', 'ManageShareController@attachmentUrlRedirect');
	Route::post('verify_user', 'Auth\LoginController@verifyUser');
	Route::get('feedback_reminder_share_admins', 'FeedbackController@feedbackAdminReminder');
	Route::get('campaign_trigger', 'ManageShareController@campaignTrigger');
	Route::get('invite_user_campaign', 'ManageShareController@inviteUserCampaign');
	Route::get('post_attachment/{id}', 'PostController@postAttachment');
	Route::post('post_view_callback','ManageShareController@postViewCallback');
	Route::any('mail_drop_log',function(Request $request ){
		(new \App\Helpers\Mailgun)->log_drop($request);
	});
	Route::any('mail_drop_log_postmark',function(Request $request ){
		(new \App\Helpers\Postmark)->logDrop($request);
	});
	Route::get('mail_drop_log_postmark/{message_id}',function($message_id){
		return (new \App\Helpers\Postmark)->getPostmarkMessageDetails($message_id);
	});
});


/* General group */
Route::group([], function () {
	Route::get('weeklysummary',"ManageShareController@weeklySummary");
	Route::get('makeroundimage',"ManageShareController@makeroundimage");
	Route::get('graphs/{spaceid}/{month}/{year}/{userid}/{comapny_id?}','AnalyticsController@graphs');
	Route::post('delete_space','ManageShareController@delete_space');
	
	Route::get('feedback_close_notification', 'FeedbackController@feedbackCloseNotification');
	Route::get('logout', 'Auth\LoginController@logout');
	Route::get('registeruser/{user_id?}/{share_id?}', 'UserController@registerUser');

	Route::get('register', function(){
		return redirect('/');
	});
	Auth::routes();

	Route::get('cancel_invitation_from_mail/{space_user_id}', 'ManageShareController@cancelInvitationFromMail');
	Route::group(['middleware'=>['verify_csrf_token','user_type_template','admin_two_way_auth_redirect']], function () {
		
		Route::post('custom_logger','LoggerController@custom_log');
		Route::get('logs', 'LoggerController@index');
		Route::post('updateregisteruser','UserController@updateRegisterUser');
		Route::post('verify_user_register_code',function(Request $request){
			return [ 'is_match'=>\App\SpaceUser::verifyRegistration($request->all()) ];
		});
		Route::post('updateprofile','UserController@updateprofile');
		Route::post('add_words', 'UserController@add_words');
		Route::any('verify_code', 'Auth\LoginController@verifyauthcode');
	});

	Route::group(['middleware'=>['auth','verify_csrf_token','user_type_template','admin_two_way_auth_redirect']], function () {

		Route::get('promote_admin','ManageShareController@promoteUserAsAdmin');
		Route::get('inactive_user','ManageShareController@inactiveUser');

		Route::get('download_attachment/{id}', 'LinkManagerController@protectedEmailAttachment');
		Route::get('display_assert', 'LinkManagerController@displayAssert');
		
		Route::get('check_login', function(){
			return ['is_login'=>Auth::check()];
		});
		Route::get('analytics/{share_id?}','AnalyticsController@index');
		Route::get('view_edit_share','ManageShareController@view_edit_share');	
		Route::post('save_background_image','ManageShareController@save_background_image');
		Route::post('updateshare','ManageShareController@updateshare');
		Route::get('dashboard', 'ManageShareController@show');
        Route::get('/', function(Request $request){
			$call_back = getVersion(['v1'=>'ManageShareController@show','v2'=>'v2\SpaceController@show'], $request->space_id);
			return App::call(Route::current()->action['namespace']."\\".$call_back , Route::current()->parameters);
		});
		Route::get('/file_view_count', 'ManageShareController@fileViewCount');
		Route::post('/save_quick_links', 'ManageShareController@saveQuickLinks');
		Route::get('/get_quick_links', 'ManageShareController@QuickLinks');
		Route::get('/community_member', 'ManageShareController@communityMember');
		Route::post('global_analytics/{graph?}','AnalyticsController@global_analytics_v3');
		Route::post('graphs', 'AnalyticsController@graphs');
		Route::any('export_xls_file','AnalyticsController@exportXlsFile');
		Route::post('domain_update', 'SettingController@domain_update');
		Route::resource('initial_setup', 'ManageShareController@initial_setup');	
		Route::get('profile', 'UserController@profile');
		Route::post('update_admin_space_profile','UserController@update_admin_space_profile' );
		Route::post('update_member_space_profile','UserController@update_member_space_profile' );
		Route::get('resend_invite_from_james','ManageShareController@resend_invite_from_james');
		Route::get('admin_landing_addpost_content/{space_id}', 'ManageShareController@admin_landing_addpost_content');
		Route::get('admin_dashboard', 'ManageShareController@show');	
		Route::get('download_graphs/{id}', 'AnalyticsController@downloadGraphs');
		Route::post('addprofile','UserController@addprofile');
		Route::get('addprofile','UserController@addprofile');
		Route::get('downloadpdf/{sharename}/{month}/{year}','FeedbackPdfController@feedbackPdf');
		Route::get('feedback_setting','SettingController@feedbackSetting');
		Route::get('feedback_tempDate_update','SettingController@feedbackDateUpdate');
		Route::get('pendinginvites','ManageShareController@pendingInvites');
		Route::get('settings', 'UserController@settings');		
		Route::get('search_block_words', 'UserController@searchBlockWords');
		Route::get('public_constants', function(){ 
			return Generic::publicConstant();
		});

		/*Ver: 2 routes*/
		Route::get('/community_member_tile/{space_id}', 'v2\SpaceUserController@communityMemberTile');
		Route::post('/remove-file', 'v2\AwsController@removeFile');
		Route::post('/delete-allowed-domain/{space_id}', 'v2\SettingController@deleteAllowedDomain')->name('delete_allowed_domain');
    });

	Route::group(['middleware'=>['auth','admin_two_way_auth_redirect']], function () {
		Route::post('mixPannelInitial', function(Request $request){
			return (new Logger)->mixPannelInitial(Auth::user()->id, $request->space_id, $request->event_tag, $request->metadata??null);
		});
		Route::any('executeSearch','SearchController@executeSearch');
		Route::any('updateCompanyId','AnalyticsController@updateCompanyId');		
		Route::any('search_sub_comp','ManageShareController@search_sub_comp');
		Route::post('saveFeedback','FeedbackController@saveFeedback');

		/*Ver: 2 routes*/
		Route::get('global-search/{keywords}/{space_id}/{user_id}/{count}', 'v2\PostController@globalSearch')->name('global.search'); //executeSearch

	});
});
/* General group end */



/* Admin/user group */
Route::group([ 'middleware'=>['role_wise_filter:admin,user'] ], function () {
	Route::group(['middleware'=>['auth','verify_csrf_token','user_type_template','admin_two_way_auth_redirect']], function () {

		Route::get('remove_report/{space_id}/{report_id}', 'SettingController@removeReport');
		
		Route::get('power_reports/{space_id}', function(Request $request){
			$call_back = getVersion(['v1'=>'SettingController@powerReports','v2'=>'v2\SettingController@powerReports'], $request->space_id);
			return App::call(Route::current()->action['namespace']."\\".$call_back , Route::current()->parameters);
		});
		

		Route::post('create_report', 'SettingController@createreport');
		Route::get('get_report_list/{space_id}', 'SettingController@getReportList');

		Route::get('single_post_edit_template/{space_id}', 'SpaceController@editSinglePostTemplate');
		
		Route::post('restict_domain', 'SpaceController@updateSpace');

		Route::get('update_tour_step/{space_id}/{step}', 'SpaceController@updateTourStep');

        Route::get('get_edit_post_template/{space_id}', 'ManageShareController@getEditPostTemplate');
        Route::get('get_add_post_template/{space_id}', 'ManageShareController@getAddPostTemplate');
		Route::post('feedback_popup/{space_id}', 'FeedbackController@feedbackPopup');

		Route::get('posts', 'PostController@posts');
		Route::get('post/{id}', 'PostController@post');
		Route::get('post_wrap', 'PostController@postWrap');
		
		Route::get('post_files/{space_id}', function(Request $request){
			$call_back = getVersion(['v1'=>'PostController@postFileView','v2'=>'v2\PostMediaController@index'], $request->space_id);
			return App::call(Route::current()->action['namespace']."\\".$call_back , Route::current()->parameters);
		})->name('post_files')->middleware('IpRestriction');
		Route::post('files-data', 'v2\PostMediaController@getFileData')->name('file.data');
		Route::post('post_files_data', 'PostController@postFileViewData');
		Route::get('space_user_and_categories/{space_id}', 'ManageShareController@spaceUserAndCategories');

		Route::get('mention_user',  function(Request $request){
			return (new PostHelper)->postUser($request);
		});
		Route::post('matchwordsubject','PostController@matchWordSubject');
    	Route::post('addpost','PostController@addPost');
    	Route::get('convert_mov_video','PostController@awsCreateJobForVideoConvert');
    	Route::post('updatepost','PostController@updatePost');

    	Route::post('allow_posting', 'SettingController@allowPosting');

    	Route::get('file_loading', function(Request $request) {
			return (new Generic)->fileLoading($request);
    	});

    	Route::post('setting/update_password','ManageShareController@update_password');
		Route::get('view_url_embeded/{post_id}',"PostController@viewUrlEmbeded");
		Route::get('url_validate/{url?}',"PostController@urlValidate");
		Route::get('log_post_evnt', 'PostController@postEventLog');
		Route::get('log_post_file_evnt', 'PostController@logPostFileEvent');
		Route::get('get_ajax_posts/{space_id}', 'ManageShareController@spacePosts');
		Route::get('cancel_invitation/{space_user_id}', 'SettingController@cancelInvitation');
		Route::get('clientshare/{space_id}/{post_id?}/{notification_id?}', function(Request $request){
			$call_back = getVersion(['v1'=>'ManageShareController@show','v2'=>'v2\SpaceController@show'], $request->space_id);
			return App::call(Route::current()->action['namespace']."\\".$call_back , Route::current()->parameters);
		})->middleware('MailLinkRedirection');
		Route::get('get_url_data/{content?}', 'PostController@getUrlData');
		Route::any('invite_user','ManageShareController@inviteUser' );
		Route::any('invite_admin_user','ManageShareController@inviteAdminUser' );
		Route::any('createlink','ManageShareController@createInviteLink' );
		Route::get('set_clientshare_list', 'ManageShareController@setClientshareList');
		Route::post('executive_summary_save', 'ManageShareController@executive_summary_save');
		Route::any('searchcommunitymember/{id}', 'ManageShareController@searchCommunityMember');
		Route::post('update_share_user', 'UserController@update_share_user');
		Route::get('add_comments','ManageShareController@add_comments' );
		Route::resource('comments','PostCommentController' );
		Route::get('endorse','ManageShareController@endorse' );
		Route::post('endorsePost','ManageShareController@endorsePost' );
		Route::get('endorsepopup_ajax','ManageShareController@endorsepopup_ajax' );
		Route::get('activity_notification','ManageShareController@activityNotification' );
		Route::get('community_members','ManageShareController@communityMembers' );
		Route::get('notification_count','ManageShareController@notificationCount' );
		Route::any('get_single_post_ajax/{postid}','ManageShareController@getSinglePostAjax' );
		Route::get('edit_visibillitypopup_ajax','ManageShareController@editVisibillitypopupAjax' );
		Route::get('endorse_setting_popup_ajax','PostController@postUsers' );
		Route::get('visibility_popupmore_ajax','ManageShareController@visibility_popupmore_ajax' );
		Route::get('editcategory_ajax','ManageShareController@editcategory_ajax' );
		Route::post('save_editcategory_ajax','ManageShareController@save_editcategory_ajax' );
		Route::post('save_categories', 'SpaceController@saveCategories');
		Route::post('delete_category','ManageShareController@deleteCategory' );
		Route::get('cancel_editcategory_ajax','ManageShareController@cancel_editcategory_ajax');
		Route::get('delete_post/{id}','PostController@deletePost');
		Route::get('delete_media/{id}','MediaController@delete_media'); 
		Route::get('delete_postmedia/{id}','PostMediaController@delete_postmedia'); 
		Route::get('delete_postmedia_all/{id}','PostMediaController@delete_postmedia_all'); 
		Route::get('view_file','ManageShareController@view_file' );
		Route::get('view_users_post','PostController@postViewerList' );
		Route::get('view_eye_users_pop','PostController@viewEyeUserPopup' ); 
		Route::get('view_url_embeded_users','PostController@viewUrlEmbededUsers');
		 
		Route::any('community_members/{space_id}', function(Request $request){
			$call_back = getVersion(['v1'=>'ManageShareController@listCommunityMembers','v2'=>'v2\SpaceUserController@communityView'], $request->space_id);
			return App::call(Route::current()->action['namespace']."\\".$call_back , Route::current()->parameters);
		})->middleware('IpRestriction');

		Route::get('community-members/{space_id}', 'v2\SpaceUserController@communityList');
		Route::get('community-space-info/{space_id}', 'v2\SpaceController@communitySpaceInfo');

		Route::get('update_admin_share','ManageShareController@update_admin_share');
		Route::get('notifications_badge_reset/{space_id}','ManageShareController@resetNotificationsBadge');
		Route::get('view_community_profile','ManageShareController@view_community_popup');
		Route::get('check_pass','ManageShareController@check_pass');
		
		Route::get('update_comments','ManageShareController@update_comments');
		Route::post('notification_setting','SettingController@notificationSettings');
		Route::get('convert_video','MediaController@convert_video');
		Route::get('get_liked_user_info','ManageShareController@getLikedUserInfo');
		Route::post('disable_account','ManageShareController@disable_account');
		Route::get('companyupdate','SettingController@companyUpdate');
		Route::get('edit_visibility','ManageShareController@edit_visibility');
		Route::get('get_all_share_notifications','ManageShareController@getAllShareNotifications');
		Route::get('update_showtour','ManageShareController@updateShowtour');
		Route::any('pinpost/{id}/{flag}/{space_id}','PostController@pinPost');
		Route::get('expandpost','ManageShareController@expandPost');
		Route::post('addgroup','PostController@AddGroup');
		Route::get('get_group_members','PostController@GetGroupMembers');
		Route::post('updategroup','PostController@UpdateGroup');
		Route::get('deletegroup','PostController@DeleteGroup');
		Route::get('getgroupbyid','PostController@GetGroupById');
		Route::get('groupmemberall','PostController@GetGroupMeversAll');
		Route::get('gettopthreepost','ManageShareController@getTopPost');
		Route::get('set_linkedin_session','SocialAuthController@setLinkedinSession');
		

		Route::resource('clientshare', 'ManageShareController', [
			'except'=>'index',
			'names' => [
		        'show' => 'landing_page'
		    ]
		]);



		Route::get('setting/{space_id}', function(Request $request){
			$call_back = getVersion(['v1'=>'SettingController@index','v2'=>'v2\SettingController@index'], $request->space_id);
			return App::call(Route::current()->action['namespace']."\\".$call_back , Route::current()->parameters);
		})->middleware('MailLinkRedirection')->middleware('IpRestriction');

		Route::any('user_management', function(Request $request){
			$call_back = getVersion(['v1'=>'SettingController@userManagement','v2'=>'v2\SettingController@userManagement'], $request->space_id);
			return App::call(Route::current()->action['namespace']."\\".$call_back , Route::current()->parameters);
		})->middleware('MailLinkRedirection')->middleware('MailLinkRedirection');

		Route::any('pending_invites', function(Request $request){
			$call_back = getVersion(['v1'=>'SettingController@pendingInvites','v2'=>'v2\SettingController@pendingInvites'], $request->space_id);
			return App::call(Route::current()->action['namespace']."\\".$call_back , Route::current()->parameters);
		})->middleware('MailLinkRedirection')->middleware('MailLinkRedirection');
		
		Route::get('feedback/{month?}/{year?}/{space_id?}','FeedbackController@feedback')->middleware('MailLinkRedirection')->middleware('IpRestriction');
		Route::get('check_space_deleted','UserController@checkSpaceDeleted');
		Route::get('send_feedback_reminder/{space_id}', 'FeedbackController@feedbackReminder');
		Route::get('feedback_current_status/{space_id}', 'FeedbackController@feedback_current_status');	
		Route::get('auth/{provider}', 'SocialAuthController@redirectToProvider');
		Route::get('auth/{provider}/callback', 'SocialAuthController@handleProviderCallback');
		Route::get('get_media/{file_id}/{modal?}', 'PostController@downloadFile');
		Route::post('setting/bulk_invitations', 'SettingController@bulkInvitations');
		Route::post('setting/send_invitations', 'SettingController@sendInvitations');
		Route::post('setting/useraddshare', 'SettingController@userAddShare');
		Route::get('auto_feedback_reminder/{space_id}', 'SettingController@autoTriggerFeedbackReminder')->middleware('MailLinkRedirection');
		Route::get('get_executive_summary/{space_id}', 'ManageShareController@executiveSummary');
		Route::post('save_twitter_feed', 'ManageShareController@saveTwitterHandles');
		Route::get('get_twitter_feeds', 'ManageShareController@twitterHandles');
		Route::get('get_space_users/{space_id}', 'UserController@getSpaceUsers');
		Route::post('share_header', 'ManageShareController@updateShareHeader');
		Route::get('s3_form_data', 'ManageShareController@getS3FormData');
		Route::get('twitter_api', 'ManageShareController@twitterAPI');
		Route::get('get_share_profile_status', 'ManageShareController@getShareProfileStatus');
        
        
        /*Ver: 2 routes*/
        Route::get('space-categories/{space_id}','v2\SpaceCategoryController@getCategories');
        Route::get('share-notifications/{space_id}/{user_id}','v2\NotificationController@getAllShareNotifications')->name('share_notifications'); 
        Route::get('get-profile/{space_id}','v2\SpaceUserController@userProfile')->name('share_user_profile');
		Route::post('update-share-user', 'v2\SpaceUserController@updateShareUser')->name('update_share_user'); 
		Route::get('get-share-notifications/{space_id}/{user_id}/{offset?}/{limit?}', 'v2\NotificationController@getShareNotifications')->name('share.notifications');
        Route::get('search-space-users/{space_id}/{search_key?}','v2\SpaceUserController@searchUser')->name('share.search.users');
    	Route::post('group-create', 'v2\GroupController@store')->name('groups.create_group');
        Route::get('get-user-groups/{space_id}/{user_id}', 'v2\GroupUserController@getUserGroups')->name('user_groups');
        Route::post('group-update', 'v2\GroupController@update')->name('groups.update_group');
        Route::delete('group-delete/{group_id}','v2\GroupController@destroy');
		Route::get('group-members/{space_id}/{group_id}', 'v2\GroupUserController@groupMembers');
        Route::delete('delete-groups-member/{group_user_id}', 'v2\GroupUserController@destroy');
        Route::post('post', 'v2\PostController@savePost');
        Route::get('aws-access', 'v2\AwsController@getToken');
        Route::get('get-url-data/{content?}', 'PostController@getUrlData');
        Route::get('get-posts', 'v2\PostController@getPosts');
        Route::get('version-switch/{space_id}/{version}', 'v2\SpaceController@updateShareVersion');
        Route::get('get-post/{space_id}/{post_id}', 'v2\PostController@getPost');
        Route::post('create-business-review', 'v2\BusinessReviewController@store')->name('create_business_review');
        Route::get('list-business-reviews/{user_id}/{space_id}/{offset?}/{limit?}', 'v2\BusinessReviewController@index')->name('list_business_reviews');
        Route::post('get-viewer', 'v2\FileViewerController@getViewer');
		Route::get('post-attachment/{url}', 'v2\FileViewerController@postAttachment')->name('post-attachment');
        Route::get('pin-post/{space_id}/{post_id}/{status}', 'v2\PostController@pinPost')->name('pin-post');
		Route::any('download-file', 'v2\FileViewerController@downloadFile')->name('download-file');
		Route::post('save-twitter-handler', 'v2\SpaceController@saveTwitterHandler')->name('save.twitter.handler');
		Route::get('get-twitter-handler/{space_id}', 'v2\SpaceController@getTwitterHandler')->name('get.twitter.handler');
        Route::post('add-attendees', 'v2\AttendeeController@store')->name('add_attendees');
        Route::get('list-attendees/{business_review_id}', 'v2\AttendeeController@index')->name('list_attendees');
        Route::delete('post/{post_id}', 'v2\PostController@delete')->name('delete-post');
        Route::delete('delete-attendees/{business_review_id}/{space_user_id}', 'v2\AttendeeController@destroy')->name('delete_attendee');
        Route::get('business-review/{id}', 'v2\BusinessReviewController@show')->name('show_business_review');
		Route::patch('post/{post_id}', 'v2\PostController@update');
        Route::delete('business-review/{id}', 'v2\BusinessReviewController@destroy')->name('delete_business_review');
        Route::post('endorse-post', 'ManageShareController@endorsePost')->defaults('api_response', true);
        Route::patch('business-review/{id}', 'v2\BusinessReviewController@update')->name('update_business_review');
		Route::get('get-endorse-users/{space_id}/{post_id}', 'v2\PostController@getEndorseUsers')->name('get-endorse-users');
		Route::get('get-space-users/{space_id}', 'v2\SpaceUserController@getSpaceUsers')->name('get-space-users');
		Route::post('add-comment', 'v2\CommentController@store')->name('add-comment');
		Route::post('post-view','v2\PostViewController@store' )->name('post-view');
		Route::patch('update-comment/{comment_id}', 'v2\CommentController@update')->name('update-comment');
		Route::delete('delete-comment/{comment_id}', 'v2\CommentController@delete')->name('delete-comment');
        Route::get('share-profile-status', 'v2\SpaceController@shareProfileStatus');
        Route::get('update-tour-step/{space_id}/{step}', 'v2\SpaceController@updateTourStep')->name('update_tour_step');
        Route::post('update-invite-admin-status/{space_id}', 'v2\SpaceController@updateInviteAdminStatus')->name('update_invite_admin_status');
        Route::get('user_information/{space_id}/{user_id}','v2\SpaceUserController@spaceUserInformation')->name('user_information');
        Route::get('reset-notifications-badge/{space_id}/{user_id}','v2\NotificationController@resetNotificationsBadge');
        Route::post('user-allow-posting', 'v2\SettingController@allowPosting')->name('user_posting_permission');
    });
});
/* Admin/user group end */

/* Super-Admin group */
Route::group([ 'middleware'=>['role_wise_filter:super_admin'] ], function () {
	Route::group(['middleware'=>['auth','verify_csrf_token','user_type_template','admin_two_way_auth_redirect']], function () {
		Route::get('deleteword/{id}','UserController@deleteword');
		Route::get('clearbitapi','ManageShareController@shareLogo');
		Route::post('clientshare','ManageShareController@store');
		Route::any('email','ManagementInformation@performanceEmail');
		Route::resource('management-information', 'ManagementInformation');
		Route::post('migrate_user', 'ManageShareController@copyUserToNewShare');
		Route::post('mi_ajax', 'ManagementInformation@show');
		Route::get('space_user_ajax', 'ManagementInformation@spaceUser');
		Route::post('mi/download/excel', 'ManagementInformation@downloadExcel');
		Route::get('share_communities/{share_id}', 'ManageShareController@shareCommunities');
		Route::get('user-search', 'ManageShareController@userSearchForm');
		Route::post('space-search-by-user', 'ManageShareController@spaceSearchByUser');
		Route::post('update-share-version','ManageShareController@UpdateShareVersion');
		Route::get('create-share-category-sheet', 'v2\SpaceController@shareCategorySheet');
        //V2 routes
        Route::post('business-review-visibility/{space_id}', 'v2\SpaceController@updateBusinessReviewVisibility')->name('update_business_review_visibility');
        Route::get('get-business-review-visibility/{space_id}', 'v2\SpaceController@isBusinessReviewEnabled')->name('is_business_review_enabled');
		Route::post('update-categories', 'v2\SpaceController@updateCategories')->name('update.categories');
		Route::get('get-categories/{space_id}', 'v2\SpaceController@getCategories')->name('get.categories');
	});

});

Route::get('csrf-token',function(){
	if(env('APP_ENV') != 'local'){
		abort(404);
	}
	return csrf_token();
});
