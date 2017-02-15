<?php
namespace RollCall\Repositories;

use RollCall\Models\User;
use RollCall\Models\Contact;
use Illuminate\Support\Facades\Storage;
use RollCall\Contracts\Repositories\UserRepository;
use Validator;

class EloquentUserRepository implements UserRepository
{
    public function all()
    {
        $users = User::all();

        return $users->toArray();
    }
    private function storeUserAvatar($file, $id)
    {
        $filename = $id;
        list($extension, $file) = explode(';', $file);
        list(, $extension) = explode('/', $extension);
        list(, $file) = explode(',', $file);
        $file = base64_decode($file);
        $path = '/useravatar/'.$filename . '.' . $extension;
        Storage::put($path, $file, 'public');
        return $path;

    }
    public function update(array $input, $id)
    {

        $user = User::findorFail($id);

        /* Updating user-avatar */
        if(isset($input['inputImage']))
        {
            $file = $input['inputImage'];
            $path = $this->storeUserAvatar($file, $id);
            $input['profile_picture'] = $path;
            unset($input['inputImage']);
        }
        /* end of user-avatar-code */

        $user->update($input);
        if (isset($input['notifications'])) {
            $user->unreadNotifications->markAsRead();
        }
        return $user->toArray();
    }

    public function create(array $input)
    {
        $file = null;
        if(isset($input['inputImage'])) {
            $file = $input['inputImage'];
            unset($input['inputImage']);
        }
        $user = User::create($input);

         if($file) {
            $path = $this->storeUserAvatar($file, $user['id']);
            $input['profile_picture'] = $path;
            $user->update($input);
         }

        return $user->toArray();
    }

    public function find($id)
    {
        return User::with('contacts')
            ->with('notifications')
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
