<?php
namespace RollCall\Repositories;

use RollCall\Models\Organization;
use RollCall\Models\User;
use RollCall\Contracts\Repositories\PersonRepository;
use RollCall\Contracts\Repositories\ContactRepository;
use RollCall\Contracts\Repositories\RollCallRepository;
use DB;
use Validator;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Notification;
use RollCall\Notifications\PersonJoinedOrganization;
use RollCall\Notifications\PersonLeftOrganization;
use Illuminate\Support\Facades\Hash;
use RollCall\Services\StorageService;

class EloquentPersonRepository implements PersonRepository
{
    public function __construct(RollCallRepository $roll_calls, ContactRepository $contacts, StorageService $storageService)
    {
        $this->roll_calls = $roll_calls;
        $this->contacts = $contacts;
        $this->storageService = $storageService;
    }

    // OrgCrudRepository
    public function all($organization_id, $offset = 0, $limit = 0)
    {
        $query = Organization::findOrFail($organization_id)
            ->members()
            ->with('contacts')
            ->select('users.*','role')
            ->orderby('name', 'asc');

        if ($limit > 0) {
          $query
            ->offset($offset)
            ->limit($limit);
        }

        $members = $query->get();

        foreach ($members as &$member) {
            $member['has_logged_in'] = $member->hasLoggedIn();
        }

        return $members->toArray();
    }

    // OrgCrudRepository
    public function create($organization_id, array $input)
    {
        $organization = Organization::findOrFail($organization_id);

        if (!isset($input['role'])) {
            $input['role'] = 'member';
        }

        if (isset($input['_input_image'])) {
            $file = $input['_input_image'];
            $input['profile_picture'] = $this->storageService->storeBase64File($file, uniqid(), 'useravatar');
            unset($input['_input_image']);
        }

        $user = new User;
        $user->fill($input);

        $user->organization_id = $organization->id;
        $user->save();

        Notification::send($this->getAdmins($organization['id']),
            new PersonJoinedOrganization($user));

        return $user->toArray();
    }

    // OrgCrudRepository
    public function update($organization_id, array $input, $user_id)
    {
        $user = User::where('id', $user_id)
            ->where('organization_id', $organization_id)
            ->firstOrFail();

        // Update user and role details
        DB::transaction(function () use ($input, $user_id, &$user) {
            // If we're change role into owner (and we're not already the owner!)
            if (isset($input['role']) && $input['role'] == 'owner' && $user->role !== 'owner') {
                // Get current owner
                $owner = User::where('organization_id', '=', $user->organization_id)
                          ->where('role', '=', 'owner')
                          ->first();

                // ...and assign admin role before transferring ownership
                $owner->role = 'admin';
                $owner->save();
            }

            /* Updating user-avatar */
            if (isset($input['_input_image']))
            {
                $file = $input['_input_image'];
                $input['profile_picture'] = $this->storageService->storeBase64File($file, uniqid($user_id), 'useravatar');
                unset($input['_input_image']);
            }
            /* end of user-avatar-code */

            // Update user
            $user->update($input);

            // Mark notifications read
            if (isset($input['notifications'])) {
                $user->unreadNotifications->markAsRead();
            }
        });

        return $user->toArray();
    }

    // OrgCrudRepository
    public function delete($organization_id, $user_id)
    {
        $user = User::where('id', $user_id)
            ->where('organization_id', $organization_id)
            ->firstOrFail();

        $user->delete();

        Notification::send($this->getAdmins($user->organization_id),
            new PersonLeftOrganization($user));

        return $user->toArray();
    }

    // OrgCrudRepository
    public function find($organization_id, $user_id)
    {
        // This should probably be passed in as param but there
        // might not be any benefit of showing a user's full
        // roll call activity here.
        $history_limit = 1;

        $userModel = User::where('id', $user_id)
            ->where('organization_id', $organization_id)
            ->with([
                'rollcalls' => function ($query) use ($history_limit) {
                    $query->latest()->limit($history_limit);
                },
                'contacts.replies' => function ($query) use ($history_limit) {
                    $query->latest()->limit($history_limit);
                },
                'contacts'
            ])
            ->with('notifications')
            ->firstOrFail();

        $user = $userModel->toArray();

        $user['has_logged_in'] = $userModel->hasLoggedIn();

        // @todo can we remove this?
        foreach ($user['rollcalls'] as &$roll_call)
        {
            $roll_call += $this->roll_calls->getCounts($roll_call['id']);
        }
        return $user;
    }

    public function findObject($id)
    {
        return User::with('contacts')
            ->with('notifications')
            ->find($id);
    }

    protected function getAdmins($organization_id)
    {
        return User::where('organization_id', $organization_id)
            ->whereIn('role', ['admin', 'owner'])
            ->get();
    }

    // PersonRepository
    public function getMemberRole($organization_id, $user_id)
    {
        $role = User::where('organization_id', '=', $organization_id)
              ->where('id', '=', $user_id)
              ->value('role');

        return $role;
    }

    // PersonRepository
    public function testMemberInviteToken($user_id, $invite_token)
    {
        return (bool) DB::table('users')
          ->where('id', $user_id)
          ->where('invite_token', $invite_token)
          ->count();
    }

    // PersonRepository
    public function addContact($organization_id, $user_id, array $input)
    {
        $user = User::where('id', $user_id)
            ->where('organization_id', $organization_id)
            ->firstOrFail();

        $input['preferred'] = 1;
        $input['user_id'] = $user->id;
        $input['organization_id'] = $organization_id;

        $input['unsubscribe_token'] = Hash::Make(config('app.key'));

        return $this->contacts->create($input);
    }

    // PersonRepository
    public function updateContact($organization_id, $user_id, array $input,  $contact_id)
    {
        $user = User::where('id', $user_id)
            ->where('organization_id', $organization_id)
            ->firstOrFail();

        // @todo ensure contact belongs to user!
        return $this->contacts->update($input, $contact_id);
    }

    // PersonRepository
    public function deleteContact($organization_id, $user_id, $contact_id)
    {
        $user = User::where('id', $user_id)
            ->where('organization_id', $organization_id)
            ->firstOrFail();

        // @todo ensure contact belongs to user!
        return $this->contacts->delete($contact_id);
    }

}
