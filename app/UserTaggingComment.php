<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserTaggingComment extends Model {

	protected $fillable = ['user_id', 'comment_id', 'post_id'];


	public static function userTaggingCountOnPost($user_id, $post_id){
        return static::where('user_id', $user_id)
        	->where('post_id', $post_id)
        	->count();
    }

	public static function logTagging($tagging_data){
		return static::create($tagging_data);
	}

	public static function removeTaggedUser($tagged_users, $comment_id){
		return static::whereNotIn('user_id', $tagged_users)
			->where('comment_id', $comment_id)
			->delete();
	}

	public static function commentTaggedUsers($tagged_users, $comment_id){
		return static::selectRaw('distinct user_id')
			->whereIn('user_id', $tagged_users)
			->where('comment_id', $comment_id)
			->get()->toArray();
	}
	public static function postTaggedUsers($post_id, $current_user, $checkAlertSetting=false){
		$result = static::selectRaw('distinct user_id')
			->with('user')
			->where('post_id', $post_id)
			->where('user_id', '!=', $current_user->id);
		
		if($checkAlertSetting){
			$result->whereHas('spaceUser', function($q)use($post_id){
				$q->where('space_id', Post::find($post_id)->space_id);
				$q->where('comment_alert', true);
			});
		}
		
		return $result->get()->toArray();
	}
	
	public function SpaceUser(){
		return $this->belongsTo('App\SpaceUser', 'user_id', 'user_id');
	}

	public function User(){
		return $this->belongsTo('App\User');
	}
}
