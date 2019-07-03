<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{

	protected $keyType = 'string';
    protected $appends = ['comment_v1'];
	protected $fillable = ['post_id', 'user_id', 'comment'];
	protected const COMMENT_REGEX = '/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*><span[^>]+>[^>]+>[^>]+>/i';

    public function getIdAttribute($id)
    {
    	return (string) $id;
    }

    public function getCommentAttribute($comment)
    {
    	preg_match_all($this::COMMENT_REGEX, $comment, $comment_text_match);
        if(sizeof($comment_text_match) && isset($comment_text_match[0])){
            foreach ($comment_text_match[0] as $key => $value) {
                $anchor = new \SimpleXMLElement($value);
                $comment = str_replace($value, '@'.$anchor['data-id'], $comment);
            }
        }
        return $comment;
    }

    public function getCommentV1Attribute()
    {
        $comment = $this->comment;
        preg_match_all('(@\w{8}-\w{4}-\w{4}-\w{4}-\w{12})', $comment, $comment_text_match);
        if(sizeof($comment_text_match) && isset($comment_text_match[0])){
            foreach ($comment_text_match[0] as $key => $value) {
                unset($user);
                if(str_replace('@', '', $value) == config('constants.USER_ID_DEFAULT')){
                    $user['fullname'] = 'All';
                } else {
                    $user = \App\User::where('id', str_replace('@', '', $value))->select('first_name', 'last_name')->first();
                    if(!$user) continue;
                    $user['fullname'] = $user->fullname;
                }

                $replace = '<a style="text-decoration:none; color:#0D47A1" href="#!" onclick="liked_info(this);" data-id="'.str_replace('@', '', $value).'"><span class="user_tag_link">@'.$user['fullname'].'</span></a>';
                $comment = str_replace($value, $replace, $comment);
            }
        }
        return $comment;
    }

    public function attachments()
    {
        return $this->hasMany('App\Models\CommentAttachment');
    }
}