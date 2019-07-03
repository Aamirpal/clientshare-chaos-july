<?php

namespace App\Helpers;
use Illuminate\Http\Request;
use App\ActivityLog;
use App\BouncedEmail;
use App\{Space,User, SpaceUser};
use App\Jobs\MixpanelLog;

class Logger {

	const MIXPANEL_TAG = [
		'view_community' => 'View community',
		'filter_community' => ' filter on community',
		'comment_added' => 'Comment added',
		'post_created' => 'Post created',
		'analytics_opened' => 'Analytics paged opened',
		'register' => 'Register',
		'complete_profile' => 'Complete Profile',
		'logout' => 'logout',
		'invite' => 'invite',
		'pin_post' => 'Pin post',
		'delete_post' => 'Delete post',
		'edit_post' => 'Edit post',
		'settings' => 'Settings',
		'add_domain' => 'Add domain',
		'remove_user' => 'Remove user',
		'company_update' => 'Edit Company',
		'promote_admin' => 'Promote admin',
		'enable_feedback' => 'Enable feedback',
		'disable_feedback' => 'Disable feedback',
		'resend_invite' => 'Resend invite',
		'bulk_invite' => 'Bulk invite',
		'view_feedback' => 'View feedback',
		'community_search' => 'Community search',
		'view_member' => 'View member',
		'download_analytics' => 'Download Analytics',
		'download_analytics_PDF' => 'Download analytics PDF',
		'feed' => 'Feed',
		'edit_executive_summary' => 'Edit Executive Summary',
		'add_post_all_alerts' => 'Add post All Alerts',
		'add_post_some_alerts' => 'Add post some Alerts',
		'add_post_no_alerts' => 'Add post no alerts',
		'add_post_no_visibility' => 'Add post no visibility',
		'add_post_all_visibility' => 'Add post all visibility',
		'add_post_some_visibility' => 'Add post some visibility',
		'multi_share_post' => 'Multi share post',
		'export_analytics_data' => 'Export analytics data',
		'edit_category' => 'Edit category',
		'delete_category' => 'Delete category',
		'disable_account' => 'Disable account',
		'post_alert_on'=> 'Post email notification on',
		'post_alert_off'=> 'Post email notification off',
		'like_alert_on'=> 'Like email notification on',
		'like_alert_off'=> 'Like email notification off',
		'comment_alert_on'=> 'Comment email notification on',
		'comment_alert_off'=> 'Comment email notification off',
		'invite_alert_on'=> 'Accepted email notification on',
		'invite_alert_off'=> 'Accepted email notification off',
		'weekly_summary_setting_on'=>'Weekly email notification on',
		'weekly_summary_setting_off'=>'Weekly email notification off',
		'update_profile' => 'Update profile',
		'past_quater' => 'Select past quarter',
		'join_new_share' => 'Join new share',
		'single_url_invite' => 'Single URL invite',
		'generate_bulk_url_invite' => 'Generate Bulk URL Invite',
		'tag_user_comment' => 'Tag user comment'
	];


	public function log($log_data) {
		try {
			isset($log_data['metadata'])?$log_data['metadata'] = json_encode($log_data['metadata']):0;
			return ActivityLog::create($log_data);
		} catch (\Exception $e) {
			return true;
		}
	}

	
	public function bouncedEmailLog($log_data){
		try {
			return BouncedEmail::create($log_data);
		} catch (\Exception $e) {
			return true;
		}
	}


	public function mixPannelInitial($user_id, $space_id=null, $event_tag, $metadata=[]){
		$log_data = [
          'user_id' => $user_id,
          'event' => $event_tag,
          'metadata'=> $metadata, 
          'space_id' => $space_id
        ];
        dispatch(new MixpanelLog($log_data));
        return;
	}

	public function mixPannelLog($log_data){
		$mix_pannel = \Mixpanel::getInstance(env('MIXPANEL_TOKEN'));
		if(isset($log_data['user_id'])){
			$user = User::getUserInfo($log_data['user_id'], 'first');
			$mix_pannel->people->set($user['email'], array(
			    'first_name'       => $user['first_name'],
			    'last_name'        => $user['last_name'],
			    'email'            => $user['email']
			));
			$mix_pannel->identify($user['email']);
		}
        $log_data['metadata'] = [];
		if(isset($log_data['space_id']) && !empty($log_data['space_id'])) {
			$log_data['metadata'] = array_merge($log_data['metadata']??[], ['share_name'=> Space::spaceById($log_data['space_id'], 'first')['share_name']]);
		}
		return $mix_pannel->track($log_data['event'], $log_data['metadata']);
	}


	public function addPostLogger($log_data){
		// dd($log_data);

		
		if(isset($log_data['share'])){
			$this->mixPannelInitial($log_data['user']['id'], $log_data['space']['id'], $this::MIXPANEL_TAG['multi_share_post']);
		}

		if(!isset($log_data['visibility'])){
			$this->mixPannelInitial($log_data['user']['id'], $log_data['space']['id'], $this::MIXPANEL_TAG['add_post_no_visibility']);		
		}elseif($log_data['visible_to_count']-1 == (sizeOfCustom($log_data['visibility'])) ){
			$this->mixPannelInitial($log_data['user']['id'], $log_data['space']['id'], $this::MIXPANEL_TAG['add_post_all_visibility']);
		} else {
			$this->mixPannelInitial($log_data['user']['id'], $log_data['space']['id'], $this::MIXPANEL_TAG['add_post_some_visibility']);
		}

		if(!isset($log_data['alert'])){
			$this->mixPannelInitial($log_data['user']['id'], $log_data['space']['id'], $this::MIXPANEL_TAG['add_post_no_alerts']);
		}elseif($log_data['visible_to_count']-1 == (sizeOfCustom($log_data['alert'])) ){
			$this->mixPannelInitial($log_data['user']['id'], $log_data['space']['id'], $this::MIXPANEL_TAG['add_post_all_alerts']);
		} else {
			$this->mixPannelInitial($log_data['user']['id'], $log_data['space']['id'], $this::MIXPANEL_TAG['add_post_some_alerts']);
		}
	}

}