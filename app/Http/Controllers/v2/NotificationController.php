<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Repositories\Notification\NotificationInterface;
use Illuminate\Http\Request;
use App\Helpers\Logger;
class NotificationController extends Controller
{
	protected $notification;
    const LIST_LIMIT = 5;

    public function __construct(NotificationInterface $notification) {
		$this->notification = $notification;
	}

	public function getAllShareNotifications($space_id, $user_id){
        return apiResponseComposer(200, [], $this->notification->getAllShareNotifications($space_id, $user_id)); 
	}

	public function getShareNotifications(Request $request, $space_id, $user_id) {
        $limit = $request->limit ?? $this::LIST_LIMIT;
        return apiResponseComposer(200, [], $this->notification->getShareNotifications($space_id, $user_id, $request->offset ?? 0, $limit));
    }
    public function resetNotificationsBadge($space_id, $user_id) {
        (new Logger)->log([
            'action' => 'view notification',
            'description' => 'view notification'
        ]);
        $set_badge_false = $this->notification->resetShareNotification($space_id, $user_id);
        if ($set_badge_false) {
            return apiResponseComposer(200, ['success' => 'Notifications reset successfully.'], $set_badge_false);
        }
        return apiResponseComposer(200, ['success' => 'No notifications to reset.'], []);
    }

}
