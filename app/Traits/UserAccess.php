<?php

namespace Rollcall\Traits;

use RollCall\Contracts\Repositories\UserRepository;
use RollCall\Contracts\Repositories\PersonRepository;
use Dingo\Api\Auth\Auth;

trait UserAccess
{

    public function setAuth(Auth $auth) {
        $this->auth = $auth;
    }

    public function setUsers(UserRepository $users) {
        $this->users = $users;
    }

    public function setPeople(PersonRepository $people) {
        $this->people = $people;
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

    protected function isUser()
    {
        return !!$this->auth->user();
    }

    protected function getOrganizationRole($org_id)
    {
        return $this->people->getMemberRole($org_id, $this->auth->user()['id']);
    }
}
