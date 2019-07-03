<?php

namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model {

    protected $keyType = 'string';
    const TAGS = [
        'comment' => 'comment',
        'user_tagged' => 'user_tagged'
    ];

    protected $casts = [
        'user_id' => 'uuid',
    ];
    protected $fillable = ['notification_type','space_id','notification_status','user_id','post_id', 'badge_status', 'comment_count', 'from_user_id', 'last_modified_by'];

    public static function userNotificationCountOnPost($space_id, $user_id, $post_id, $notification_type){

        return static::where('space_id', $space_id)
            ->where('user_id', $user_id)
            ->where('notification_type', $notification_type)
            ->where('post_id', $post_id)
            ->selectRaw('max(comment_count) as comment_count')
            ->first();
    }

    public static function updateOrCreateNotification($notification){
        return static::updateOrCreate(
            ['post_id'=> $notification['post_id'], 'user_id'=> $notification['user_id'], 'notification_type'=> $notification['notification_type']],
            $notification
        );
    }

    public static function activityNotification($request_data, $user_id){
        $response['notification'] = DB::select("SELECT n.created_at, n.id, n.post_id, n.comment_count, n.notification_status,n.notification_type, u.first_name , u.last_name as name, u.profile_image,
            case
            when n.user_id=n.from_user_id and n.notification_type = 'comment' then
            '<b>' || INITCAP(u.first_name) || '</b> ' || (case when n.comment_count > 1 then '<b> & ' ||  n.comment_count-1 || ' other(s) ' else ''end)  || '</b> commented on your post'
            when n.user_id=n.from_user_id and n.notification_type = 'like' then
            '<b>' || INITCAP(u.first_name) || '</b> ' || (case when n.comment_count > 1 then '<b> & ' ||  n.comment_count-1 || ' other(s) ' else ''end)  || '</b> liked your post'
            when n.notification_type = 'user_tagged' then
            '<b>' || INITCAP(u.first_name) || '</b> ' || (case when n.comment_count > 1 then '<b> & ' || n.comment_count-1 || ' other(s) ' else ''end) || '</b> tagged you on a post'
            else
                '<b>' || INITCAP(u.first_name) || '</b> ' ||(case when n.comment_count>1 then '<b> & ' || n.comment_count-1 || ' other(s) ' else ''end ) || '</b> commented  on ' || INITCAP(upost.first_name) || '&lsquo;s post' end as postText
            from notifications n
            left join users u on u.id=n.last_modified_by
            left join users upost on upost.id=n.from_user_id
            where n.user_id='$user_id'  and n.space_id='$request_data->space_id'
            order by n.updated_at desc
            limit '$request_data->limit'
            offset '$request_data->offset' ");
        $response['feedback'] = DB::select("select * from feedback where date_part('month',created_at)  = date_part('month',now()) and space_id = '$request_data->space_id' and user_id = '$user_id'");
        return $response;
    }

    public static function notificationCount($space_id, $user_id){
        return DB::select("SELECT count(*) as sum  from notifications where badge_status = true and space_id = '$space_id' and user_id = '$user_id';");
    }

    public static function getAllShareNotifications($space_id, $user_id){
        return DB::select("SELECT n.space_id,count(n.id) from notifications as n join space_users as u on n.space_id=u.space_id where n.badge_status = true and n.user_id = '$user_id' and n.space_id!= '$space_id' and u.user_id = '$user_id' and u.space_id!= '$space_id' and u.metadata->>'invitation_code'='1' and u.user_status = 0 group by 1");
    }

    public function getIdAttribute($value){
    	return (string) $value;
    }
    public function user()
    {
        return $this->hasMany('App\user');
    }
    public function post()
    {
        return $this->belongsTo('App\Post');
    }
    public static function updateBadgeStatus($post_id,$user_id) {
        return static::where('post_id', $post_id)->where('user_id', '!=', $user_id)->where('notification_type', 'comment')->update(['badge_status' => 'true']);
    }
    public static function getCommentNotification($post_id,$user_id) {
        return static::where('post_id', $post_id)->where('user_id', $user_id)->where('notification_type', 'comment')->get();
    }
    public static function updateNotification($post_id,$user_id,$notification_status,$last_modified_by,$comment_count) {
        return static::where('post_id', $post_id)
                        ->where('user_id', $user_id)
                        ->where('notification_type', 'comment')
                        ->update(['notification_status' => $notification_status, 'last_modified_by' => $last_modified_by, 'comment_count' => $comment_count,
                            ]);
    }
    public static function deleteNotifications($user_id,$post_id) {
        $user_id = implode(",",array_map(function($uuid){
            return "'$uuid'";
        },$user_id));
        Notification::Where('post_id',$post_id)
        ->whereRaw(DB::raw('user_id not in ('.$user_id.')'))
        ->delete();
    }
    
    public static function markPostNotificationsAsRead($post_id, $user_id) {
        if(is_null($post_id) || is_null($user_id)) {
            return;
        }
        return static::where('post_id', $post_id)
                ->where('user_id', $user_id)
                ->where('notification_status', false)
                ->update([
                    'updated_at' => DB::raw("updated_at"),
                    'notification_status' => true
                ]);
    }

    public function getUserLikeNotification($post_id, $user_id, $type) {
        return $this->where('post_id', $post_id)->where('user_id', $user_id)->where('notification_type', $type)->first();
    }

    public function updateUserNotification($post_id, $user_id, $notification_status, $update_array) {
        return $this->where('post_id', $post_id)
                    ->where('user_id', $user_id)
                    ->where('notification_type', $notification_status)
                    ->update($update_array);
    }

    public function deleteUserNotifications($post_id ,$post_author, $type) {
        return $this->where('post_id', $post_id)->where('user_id', $post_author)->where('notification_type', $type)->delete();
    }

    public function saveUserNotifications($create_array) {
        return $this->create($create_array);
    }
}
