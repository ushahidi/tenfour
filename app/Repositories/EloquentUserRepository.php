<?php
namespace RollCall\Repositories;

use RollCall\Models\User;
use RollCall\Models\Contact;
use RollCall\Contracts\Repositories\UserRepository;

class EloquentUserRepository implements UserRepository
{
    public function all()
    {
        $users = User::all();

        return $users->toArray();
    }

    public function update(array $input, $id)
    {
        $user = User::findorFail($id);
        $user->update($input);

        return $user->toArray();
    }

    public function create(array $input)
    {
        return User::create($input)->toArray();
    }

    public function find($id)
    {
        return User::with('contacts')
            ->find($id)
            ->toArray();
    }

    public function delete($id)
    {
		$user = User::findorFail($id);
		$user->delete();

        return $user->toArray();
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
