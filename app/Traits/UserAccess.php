<?php

namespace TenFour\Traits;

use Dingo\Api\Auth\Auth;

trait UserAccess
{

    public function setAuth(Auth $auth) {
        $this->auth = $auth;
    }

    protected function isSelf($id)
    {
        return $this->auth->user()->id === (int) $id;
    }

    public function user($guard = NULL)
    {
        return $this->auth->user();
    }

    protected function isAdmin($orgId)
    {
        return $this->auth->user()->isAdmin($orgId);
    }

    protected function isMember($orgId)
    {
        return $this->auth->user()->isMember($orgId);
    }

    protected function isOwner($orgId)
    {
        return $this->auth->user()->isOwner($orgId);
    }

    protected function isUser()
    {
        return !!$this->auth->user();
    }
}
