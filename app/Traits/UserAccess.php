<?php

namespace Rollcall\Traits;

use RollCall\Contracts\Repositories\UserRepository;
use RollCall\Contracts\Repositories\OrganizationRepository;
use Dingo\Api\Auth\Auth;

trait UserAccess
{

    public function setAuth(Auth $auth) {
        $this->auth = $auth;
    }

    public function setUsers(UserRepository $users) {
        $this->users = $users;
    }

    public function setOrganizations(OrganizationRepository $organizations) {
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
