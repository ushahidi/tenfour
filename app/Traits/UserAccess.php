<?php

namespace Rollcall\Traits;

use RollCall\Contracts\Repositories\UserRepository;
use Dingo\Api\Auth\Auth;

trait UserAccess
{
    public function __construct(Auth $auth, UserRepository $users)
    {
        $this->auth = $auth;
        $this->users = $users;
    }

    protected function isSelf($id)
    {
        return $this->auth->user()->id === (int) $id;
    }

    protected function isAdmin()
    {
        $roles = $this->users->getRoles($this->auth->user()->id);
        return in_array('admin', $roles);
    }
}
