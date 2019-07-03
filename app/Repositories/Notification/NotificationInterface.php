<?php

namespace App\Repositories\Notification;

interface NotificationInterface
{
	public function getAllShareNotifications($space_id, $user_id);
	public function getShareNotifications($space_id, $user_id, $offset, $limit);
    public function resetShareNotification($space_id, $user_id);
}