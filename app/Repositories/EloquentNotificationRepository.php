<?php
namespace TenFour\Repositories;

use TenFour\Models\User;
use TenFour\Contracts\Repositories\NotificationRepository;

class EloquentNotificationRepository implements NotificationRepository
{
    public function all($person_id, $offset = 0, $limit = 0, $unread = false)
    {
        if ($unread) {
            $query = User::findOrFail($person_id)->unreadNotifications();
        } else {
            $query = User::findOrFail($person_id)->notifications();
        }

        if ($limit > 0) {
            $query = $query->offset($offset)->limit($limit);
        }

        $notifications = $query->get();

        return $notifications->toArray();
    }

    public function markAllAsRead($person_id)
    {
        $user = User::findOrFail($person_id);
        $user->unreadNotifications->markAsRead();

        return $user->unreadNotifications->toArray();
    }

    public function markAsRead($person_id, $notification_id)
    {
        $user = User::findOrFail($person_id);
        $notification = $user->notifications()->where('id', '=', $notification_id)->get();
        $notification->markAsRead();

        return $notification->toArray();
    }

}
