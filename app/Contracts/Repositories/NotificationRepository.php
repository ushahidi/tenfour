<?php
namespace TenFour\Contracts\Repositories;

interface NotificationRepository
{
    public function all($person_id, $offset = 0, $limit = 0);
}
