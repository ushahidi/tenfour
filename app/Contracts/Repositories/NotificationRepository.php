<?php
namespace TenFour\Contracts\Repositories;

interface NotificationRepository
{
    public function all($person_id, $offset = 0, $limit = 0);
    public function markAllAsRead($person_id);
    public function markAsRead($person_id, $notification_id);
}
