<?php

namespace Rollcall\Traits;

use RollCall\Contracts\Repositories\UserRepository;
use RollCall\Contracts\Repositories\OrganizationRepository;
use Dingo\Api\Auth\Auth;

trait UserAccess
{
    public function __construct(Auth $auth, UserRepository $users, OrganizationRepository $organizations)
    {
        $this->auth = $auth;
        $this->users = $users;
        $this->organizations = $organizations;
    }

    protected function isSelf($id)
    {
        return $this->auth->user()['id'] === (int) $id;
    }

    protected function isAdmin()
    {
        $roles = $this->users->getRoles($this->auth->user()['id']);
        return in_array('admin', $roles);
    }

    protected function getOrganizationRole($org_id)
    {
        return $this->organizations->getMemberRole($org_id, $this->auth->user()['id']);
    }
}
