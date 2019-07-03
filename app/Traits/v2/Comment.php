<?php
namespace App\Traits\v2;

use App\Models\RemoveCloudFile;
use App\Http\Controllers\MailerController;
use App\Jobs\v2\SendCommentEditAlert;
use App\Models\Comment as CommentModel;
use App\{Post, Space, UserTaggingComment};

trait Comment
{

	protected function deleteComment($comment_id)
	{
		return apiResponse($this->comment->deleteComment($comment_id));
	}

	public function sendEditCommentAlerts($comment_id, $current_user)
	{
		$comment = CommentModel::find($comment_id);
		$tagged_users = $this->getTaggedUser($comment);
		UserTaggingComment::removeTaggedUser($tagged_users, $comment_id);
		$comment_users = UserTaggingComment::commentTaggedUsers($tagged_users, $comment_id);
		$comment_users = array_column($comment_users, 'user_id');
		array_push($comment_users, $current_user->id);
		$tagged_users = array_diff($tagged_users, $comment_users);
		$post = Post::postById($comment->post_id);
		$space = Space::spaceById($post['space_id'], 'first');
		$alert_data = compact('space', 'comment', 'current_user', 'post');
		$this->sendTaggedAlert($alert_data, $tagged_users);
	}

	protected function updateComment($request, $comment_id)
	{
		$comment_request = $request->all();
        unset($comment_request['attachments'], $comment_request['delete_attachments'], $comment_request['space_id']);
       
        $comment = $this->comment->updateComment($comment_id, $comment_request);

        $this->comment_attachment->addAttachments($request->attachments, $comment_id);
        if($request->delete_attachments){
	      $delete_attachments = array_map(function ($attachment) {return $attachment['attachmentID'];}, $request->delete_attachments);
	      $comment_attachments = $this->comment_attachment->getAttachmentsById($delete_attachments);
	      $this->remove_cloud_files->logFiles($comment_attachments);
	      $this->comment_attachment->removeAttachments($delete_attachments, $comment_id);
	    }

	    dispatch(new SendCommentEditAlert([
            'comment_id' => $comment_id,
            'current_user' => \Auth::user()
        ], $this->group_users));
        	
        return apiResponse($this->comment->getComment($comment_id));
	}

	protected function sendAlerts($comment, $current_user)
	{
		$post = \App\Post::postById($comment->post_id);
		$space = \App\Space::spaceById($post['space_id'], 'first');
		$alert_user_list = $this->alertUserList($comment, $current_user, $post);
		$people_reacted = (new \App\User)->getPeopleReacted($post->getAllReactedUsers($comment->post_id , [$current_user->id]));
        $alert_data = compact('space', 'comment', 'current_user', 'post', 'alert_user_list','people_reacted');
		$this->sendEmailAlert($alert_data);
		$this->sendNotifications($alert_data);
	}

	private function sendEmailAlert($alert_data)
	{
		$tagged_users = $this->getTaggedUser($alert_data['comment']);
		$alert_user_list = array_unique(arrayValueToKey($alert_data['alert_user_list']['email_alert'], 'user_id'), SORT_REGULAR);
		$tagged_users = array_diff($tagged_users, [$alert_data['current_user']['id']]);
		
		if(!in_array(config('constants.USER_ID_DEFAULT'), $tagged_users)){
			foreach ($alert_user_list as $email_alert_user) {
				if(in_array($email_alert_user['user']['id'], $tagged_users)) continue;
				if(!\App\Post::checkPostVisibility($alert_data['post']['id'], $email_alert_user['user']['id'])) continue;
				$alert_data['user'] = $email_alert_user['user'];
                (new MailerController)->sendCommentEmailAlert($alert_data, 'email.alert_comments');
			}
		}

		$this->sendTaggedAlert($alert_data, $tagged_users);
	}

	private function sendNotifications($alert_data)
	{
		$tagged_users = $this->getTaggedUser($alert_data['comment']);
		foreach ($alert_data['alert_user_list']['notification_alert'] as $email_alert_user) {
			if(in_array($email_alert_user['user']['id'], $tagged_users)) continue;
			if(!\App\Post::checkPostVisibility($alert_data['post']['id'], $email_alert_user['user']['id'])) continue;
			$alert_data['user'] = $email_alert_user['user'];
			$this->sendNotificationAlert($alert_data);
	    }
	}

	private function alertUserList($comment, $current_user, $post)
	{
		$alert_user_list['email_alert'] = \App\Comment::alertUserList($comment, $current_user, true);
		$alert_user_list['notification_alert'] = \App\Comment::alertUserList($comment, $current_user);

		$post_owner = \App\Post::postOwner($comment, $current_user, $post, 'comment_alert');
		$alert_tag_user_list = \App\UserTaggingComment::postTaggedUsers($comment->post_id, $current_user, true);
		$alert_tag_user_list = array_merge($alert_user_list['email_alert'], $alert_tag_user_list, $post_owner);
		$alert_user_list['email_alert'] = array_unique($alert_tag_user_list, SORT_REGULAR);

		$alert_tag_user_list = \App\UserTaggingComment::postTaggedUsers($comment->post_id, $current_user);
		$post_owner = \App\Post::postOwner($comment, $current_user, $post);
		$alert_tag_user_list = array_merge($alert_user_list['notification_alert'], $alert_tag_user_list, $post_owner);
		$alert_user_list['notification_alert'] = array_unique($alert_tag_user_list, SORT_REGULAR);
		
		return $alert_user_list;
	}

	private function sendTaggedAlert($alert_data, $tagged_users)
	{
		$tagged_users = \App\SpaceUser::checkTagAlertSetting($alert_data['post']['space_id'], $tagged_users, true, in_array(config('constants.USER_ID_DEFAULT'), $tagged_users));
		foreach ($tagged_users as $tagged_user ) {
		  if($tagged_user['user_id'] != $alert_data['current_user']['id']){
			if(!$this->group_users->getGroupUser($alert_data['post']['group_id'], $tagged_user['user_id'], 'count')) continue;
			$alert_data['mail_headers'] = [
		      'X-PM-Tag' => 'user-tag-alert',
		      'space_id' => $alert_data['space']['id']
		    ];
		    $alert_data['mail_subject'] = ucfirst($alert_data['current_user']['first_name']).' '.ucfirst($alert_data['current_user']['last_name']).' tagged you in a comment';
			$alert_data['user'] = \App\User::getUserInfo($tagged_user['user_id'], 'first')->toArray();
			
                \App\UserTaggingComment::logTagging([
				'user_id' => $alert_data['user']['id'],
				'comment_id' => $alert_data['comment']['id'],
				'post_id' => $alert_data['post']['id']
			]);
           
            $alert_data['people_reacted'] = (new \App\User)->getPeopleReacted((new \App\Post)->getAllReactedUsers($alert_data['post']['id'], [$alert_data['current_user']['id'], $alert_data['user']['id']]));
                (new MailerController)->sendCommentEmailAlert($alert_data, 'email.comment_user_tagging');
                $this->sendNotificationAlert($alert_data, 'user_tagged');
            }
		}
	}

	private function sendNotificationAlert($alert_data, $notification_type=null)
	{

		$notification['post_id'] = $alert_data['post']['id'];
		$notification['user_id'] = $alert_data['user']['id'];
		$notification['notification_status'] = FALSE;
		$notification['space_id'] = $alert_data['space']['id'];
		$notification['notification_type'] =  $notification_type??config('constants.COMMENT');
		$notification['from_user_id'] = $alert_data['post']['user_id'];
		$notification['last_modified_by'] = $alert_data['current_user']['id'];
		$notification['badge_status'] = true;

		if( $notification['notification_type'] == \App\Notification::TAGS['user_tagged'] ){
			$count = \App\UserTaggingComment::userTaggingCountOnPost($notification['user_id'], $notification['post_id']);
		}
		else{
			$count = \App\Notification::userNotificationCountOnPost($notification['space_id'], $notification['user_id'], $notification['post_id'], $notification['notification_type']);
			$count = $count['comment_count']??1;
		}

		$notification['comment_count'] = $count??1;
		\App\Notification::updateOrCreateNotification($notification);
	}

	private function getTaggedUser($comment)
	{
		preg_match_all('(\w{8}-\w{4}-\w{4}-\w{4}-\w{12})', $comment->comment, $users);
		return array_unique(array_pop($users));
	}

}