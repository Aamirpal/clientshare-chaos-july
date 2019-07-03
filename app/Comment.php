<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class Comment extends Model {

    protected $keyType = 'string';
    public function updateComment($comment_id, $updated_data) {
        return $this->where('id', $comment_id)->update($updated_data);
    }

    public static function addComment($comment_data){
        $comment = new Comment;
        $comment->post_id = $comment_data['post_id'];
        $comment->user_id = $comment_data['user_id'];
        $comment->comment = trim($comment_data['comment']);
        $comment->save();
        return $comment;
    }

    public static function alertUserList($comment, $current_user, $check_alert_enable=false){
        return static::selectRaw('distinct user_id')
            ->where('post_id', $comment->post_id)
            ->where('user_id', '!=', $current_user->id)
            ->with('user')
            ->whereHas('spaceuser', function($q)use($check_alert_enable, $comment){
                $q->where('space_id', Post::find($comment->post_id)->space_id);
                if($check_alert_enable)
                    $q->where('comment_alert', true);
            })
        ->get()->toArray();        
    }


    public static function postComments($space_id, $post_id, $row_limit = 'all'){
        return DB::select("SELECT *,(select distinct user_status from space_users where space_id = '$space_id' and user_id = t.user_id) as spaceusers FROM (SELECT *,id as commentid,created_at as comment_created,updated_at as comment_updated FROM comments WHERE post_id ='$post_id' order by created_at desc limit $row_limit )t INNER JOIN users ON t.user_id = users.id order by t.created_at");
    }

    public function getCreatedAtAttribute($value) {
        if(!$value) return $value;
        return Carbon::parse($value)->timezone(\Auth::user()->timezone??config('constants.TIMEZONE'));
    }
    
    public function getUpdatedAtAttribute($value) {        
        if(!$value) return $value;
        return Carbon::parse($value)->timezone(\Auth::user()->timezone??config('constants.TIMEZONE'));
    }

    public static function deleteCommentByPost($post_id){
        $comment = static::where('post_id',$post_id); 
        return $comment->delete();
    }

    public function attachments(){
        return $this->hasMany('App\CommentAttachment', 'comment_id', 'id');
    }

    public function getIdAttribute($value){
    	return (string) $value;
    }

    public function getCommentAttribute($comment){
        preg_match_all('(@\w{8}-\w{4}-\w{4}-\w{4}-\w{12})', $comment, $comment_text_match);
        if(sizeof($comment_text_match) && isset($comment_text_match[0])){
            foreach ($comment_text_match[0] as $key => $value) {
                unset($user);
                if(str_replace('@', '', $value) == config('constants.USER_ID_DEFAULT')){
                    $user['fullname'] = 'All';
                } else {
                    $user = \App\User::where('id', str_replace('@', '', $value))->select('first_name', 'last_name')->first();
                    if(!$user){
                        continue;
                    }
                    $user['fullname'] = $user->fullname;
                }   
                
                $replace = '<a style="text-decoration:none; color:#0D47A1" href="#!" onclick="liked_info(this);" data-id="'.$value.'"><span class="user_tag_link">'.$user['fullname'].'</span></a>';
                $comment = str_replace($value, $replace, $comment);
            }
        }
        return $comment;
    }

    public function post()
    {
        return $this->belongsTo('App\Post');
    }
    public function user()
    {
        return $this->belongsTo('App\User');
    }
    public function spaceuser()
    {
        return $this->hasMany('App\SpaceUser','user_id','user_id');
    }
    public static function usersWhoComment($post_id,$user_id) {
        return static::where('post_id', $post_id)->where('user_id','!=', $user_id)->distinct()->pluck('user_id');
    }
    public static function sellerBuyerComments($space_id, $start_date, $end_date){
        return DB::select(
            "SELECT
                to_char(cmnt.created_at, 'YYYY-MM'),
                case when user_company_id = ( select company_buyer_id from spaces where id = '".$space_id."' )
                    then 'Buyer'
                when user_company_id = ( select company_seller_id from spaces where id = '".$space_id."')
                    then 'Seller'
                end as tag, count(*)
            from comments cmnt
            inner join posts post on post.id = cmnt.post_id
            inner join space_users su on post.space_id = su.space_id and cmnt.user_id = su.user_id
            where su.space_id = '".$space_id."'
            and user_company_id != '00000000-0000-0000-0000-000000000000'
            and cmnt.created_at between  '".$start_date."' and  '".$end_date."'
            group by tag,to_char(cmnt.created_at, 'YYYY-MM')
            order by to_char(cmnt.created_at, 'YYYY-MM');"
        );
    }
}
