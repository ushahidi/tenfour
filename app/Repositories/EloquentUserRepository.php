<?php
namespace RollCall\Repositories;

use RollCall\Models\User;
use RollCall\Contracts\Repositories\UserRepository;

class EloquentUserRepository implements UserRepository
{
    public function all()
    {
        return User::all();
    }

    public function update(array $input, $id)
    {
		$user = User::findorFail($id);
        $user->update($input);
		return $user;
    }

    public function create(array $input)
    {
        return User::create($input);
    }

    public function find($id)
    {
        return User::find($id);
    }

    public function delete($id)
    {
		$user = User::findorFail($id);
		$user->delete();
        return $user;
    }

    public function getRoles($id)
    {
        $roles = [];

        $user = User::find($id);

        if (! $user) {
            return $roles;
        }

        foreach ($user->roles as $role)
        {
            array_push($roles, $role->name);
        }

        return $roles;
    }
}
