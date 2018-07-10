<?php
namespace TenFour\Repositories;

use TenFour\Models\User;
use TenFour\Contracts\Repositories\NotificationRepository;

class EloquentNotificationRepository implements NotificationRepository
{
    public function all($person_id, $offset = 0, $limit = 0)
    {
        $query = User::findOrFail($person_id)->notifications();

        if ($limit > 0) {
            $query = $query->offset($offset)->limit($limit);
        }

        $notifications = $query->get();

        return $notifications->toArray();
    }


}
