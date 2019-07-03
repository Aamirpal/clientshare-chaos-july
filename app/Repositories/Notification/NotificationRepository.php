<?php

namespace App\Repositories\Notification;

use App\Models\Notification;
use App\Models\SpaceUser;
use App\Repositories\Notification\NotificationInterface;
use DB;

class NotificationRepository implements NotificationInterface {

    protected $notification;

    public function __construct(Notification $notification) {
        $this->notification = $notification;
    }
    
    public function getAllShareNotifications($space_id, $user_id) {
        return Notification::from($this->notification->getTable() . ' as notification')
                ->select(DB::raw('notification.space_id , count(notification.id)'))
                ->join((new SpaceUser)->getTable() . ' as  u', 'u.space_id', 'notification.space_id')
                ->where([
                    'notification.badge_status' => true,
                    'notification.user_id' => $user_id,
                    'u.user_id' => $user_id,
                    'u.user_status' => 0,
                ])
                ->where('notification.space_id', '<>', $space_id)
                ->where('u.space_id', '<>', $space_id)
                ->whereRaw("u.metadata->>'invitation_code'='1'")
                ->groupBy('notification.space_id')
                ->get();
    }

    public function getShareNotifications($space_id, $user_id, $offset = 0, $limit = 5) {
        $notifications = DB::select("SELECT n.created_at, n.id, n.post_id, n.comment_count, n.notification_status,n.notification_type, u.first_name , u.last_name, u.circular_profile_image as profile_image
            from notifications n
            left join users u on u.id=n.last_modified_by
            left join users upost on upost.id=n.from_user_id
            where n.user_id='$user_id'  and n.space_id='$space_id' and u.first_name is not null  and n.post_id is not null
            order by n.updated_at desc
            limit '$limit'
            offset '$offset'");
        $response['notifications'] = [];  
        foreach($notifications as $key => $notification){
            $response['notifications'][$key] = $notification;
            $response['notifications'][$key]->profile_image = $notification->profile_image ? composeUrl($notification->profile_image) : null;
        }
        $response['feedback'] = DB::select("select * from feedback where date_part('month',created_at)  = date_part('month',now()) and space_id = '$space_id' and user_id = '$user_id'");
        $response['notification_messages'] = __('notification.messages');
        $response['offset'] = $offset ? $offset + $limit : $limit;
        return $response;
    }
    public function resetShareNotification($space_id, $user_id) {
        return $this->notification->where(['space_id'=> $space_id, 'user_id'=>$user_id, 'badge_status'=>true])->update(['badge_status'=>false]);
    }
}
