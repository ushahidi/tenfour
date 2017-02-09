<?php
namespace RollCall\Repositories;

use RollCall\Models\Organization;
use RollCall\Models\User;
use RollCall\Contracts\Repositories\UserRepository;
use RollCall\Contracts\Repositories\PersonRepository;
use RollCall\Contracts\Repositories\ContactRepository;
use RollCall\Contracts\Repositories\RollCallRepository;
use DB;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Notification;
use RollCall\Notifications\PersonJoinedOrganization;
use RollCall\Notifications\PersonLeftOrganization;
use Illuminate\Support\Facades\Hash;

class EloquentPersonRepository implements PersonRepository
{
    public function __construct(UserRepository $users, RollCallRepository $roll_calls, ContactRepository $contacts)
    {
        $this->users = $users;
        $this->roll_calls = $roll_calls;
        $this->contacts = $contacts;
    }

    /**
     * Get all
     *
     * @return mixed
     */
    public function all()
    {

    }

    /**
     * Create
     *
     * @param array $input
     *
     * @return mixed
     */
    public function create(array $input)
    {

    }

    /**
     * Update
     *
     * @param array $input
     * @param int $id
     *
     * @return mixed
     */
    public function update(array $input, $id)
    {

    }

    /**
     * Delete
     *
     * @param int $id
     *
     * @return mixed
     */
    public function delete($id)
    {

    }

    /**
     * Find
     *
     * @param int $id
     *
     * @return mixed
     */
    public function find($id)
    {

    }

    public function updateMember(array $input, $id, $user_id)
    {
        $organization = Organization::with([
            'members' => function ($query) use ($user_id) {
                $query->select('users.id')->where('users.id', $user_id);
            }])->findOrFail($id);

        if ($organization->members->isEmpty()) {
            throw (new ModelNotFoundException)->setModel('User');
        }

        $user = null;

        // Update user and role details
        DB::transaction(function () use ($input, $user_id, $organization, &$user) {
            if ($input['role'] == 'owner') {
                // Get current owner
                $owner_id = DB::table('organization_user')
                          ->where('organization_id', '=', $organization->id)
                          ->where('role', '=', 'owner')
                          ->value('user_id');

                // ...and assign member role before transferring ownership
                $organization->members()->updateExistingPivot($owner_id, ['role' => 'member']);
            }

            $organization->members()->updateExistingPivot($user_id, ['role' => $input['role']]);

            // Update user
            $user_input = array_except($input, ['role']);

            $user = $this->users->update($user_input, $user_id);
        });

        return $user + [
            'role' => $input['role']
        ];
    }

    public function getMember($id, $user_id)
    {
        // This should probably be passed in as param but there
        // might not be any benefit of showing a user's full
        // roll call activity here.
        $history_limit = 1;

        $organization = Organization::with([
            'members' => function ($query) use ($user_id) {
                $query->select('users.id')
                    ->where('users.id', $user_id);
            }])->findOrFail($id);

        if ($organization->members->isEmpty()) {
            throw (new ModelNotFoundException)->setModel('User');
        }

        $role = $organization->members->first()->pivot->role;

        $userModel = User::with([
            'rollcalls' => function ($query) use ($history_limit) {
                $query->latest()->limit($history_limit);
            },
            'contacts.replies' => function ($query) use ($history_limit) {
                $query->latest()->limit($history_limit);
            },
            'contacts'
        ])
              ->find($user_id);

        $user = $userModel->toArray() + [
                  'role' => $role
              ];

        $user['has_logged_in'] = $userModel->hasLoggedIn();

        // @todo can we remove this?
        foreach ($user['rollcalls'] as &$roll_call)
        {
            $roll_call += $this->roll_calls->getCounts($roll_call['id']);
        }

        return $user;
    }

    public function addMember(array $input, $id)
    {
        $organization = Organization::findorFail($id);

        $user = null;

        if (!isset($input['role'])) {
            $input['role'] = 'member';
        }

        DB::transaction(function () use (&$user, $input, $organization) {
            $user_input = array_except($input, ['role']);

            $user = $this->users->create($user_input);

            $organization->members()->attach($user['id'], ['role' => $input['role']]);
        });

        Notification::send($this->getAdmins($organization['id']),
            new PersonJoinedOrganization(new User($user)));

        return $user + [
            'role' => $input['role']
        ];
    }

    public function getMembers($id)
    {
        return Organization::findOrFail($id)
            ->members()
            ->with('contacts')
            ->select('users.*','role')
            ->orderby('name', 'asc')
            ->get()
            ->toArray();
    }

    protected function getAdmins($id)
    {
        return Organization::findOrFail($id)
            ->members()
            ->select('users.*','role')
            ->whereIn('role', ['admin', 'owner'])
            ->get();
    }

    public function deleteMember($id, $user_id)
    {
        $organization = Organization::with([
            'members' => function ($query) use ($user_id) {
                $query->select('users.id', 'users.name')->where('users.id', $user_id);
            }])->findOrFail($id);

        if ($organization->members->isEmpty()) {
            throw (new ModelNotFoundException)->setModel('User');
        }

        $user_id = $organization->members->first()->id;
        $role = $organization->members->first()->pivot->role;

        $user = null;

        DB::transaction(function () use (&$user, $organization, $user_id) {
            $organization->members()->detach($user_id);

            // Delete user
            $user = $this->users->delete($user_id);
        });

        Notification::send($this->getAdmins($organization['id']),
            new PersonLeftOrganization(new User($user)));

        return $user + [
            'role' => $role
        ];
    }

    public function getMemberRole($organization_id, $user_id)
    {
        $role = DB::table('organization_user')
              ->where('organization_id', '=', $organization_id)
              ->where('user_id', '=', $user_id)
              ->value('role');

        return $role;
    }

    public function isMember($user_id, $org_id)
    {
        return (bool) DB::table('organization_user')
            ->where('user_id', $user_id)
            ->where('organization_id', $org_id)
            ->count();
    }

    public function testMemberInviteToken($memberId, $invite_token)
    {
        return (bool) DB::table('users')
          ->where('id', $memberId)
          ->where('invite_token', $invite_token)
          ->count();
    }

    public function addContact(array $input, $id, $user_id)
    {
        $organization = Organization::with([
            'members' => function ($query) use ($user_id) {
                $query->select('users.id')->where('users.id', $user_id);
            }])->findOrFail($id);

        if ($organization->members->isEmpty()) {
            throw (new ModelNotFoundException)->setModel('User');
        }

        $input['can_receive'] = 1;
        $input['user_id'] = $user_id;
        $input['unsubscribe_token'] = Hash::Make(config('app.key'));

        return $this->contacts->create($input);
    }

    public function updateContact(array $input, $id, $user_id, $contact_id)
    {
        $organization = Organization::with([
            'members' => function ($query) use ($user_id) {
                $query->select('users.id')->where('users.id', $user_id);
            }])->findOrFail($id);

        if ($organization->members->isEmpty()) {
            throw (new ModelNotFoundException)->setModel('User');
        }

        return $this->contacts->update($input, $contact_id);
    }

    public function deleteContact($id, $user_id, $contact_id)
    {
        $organization = Organization::with([
            'members' => function ($query) use ($user_id) {
                $query->select('users.id')->where('users.id', $user_id);
            }])->findOrFail($id);

        if ($organization->members->isEmpty()) {
            throw (new ModelNotFoundException)->setModel('User');
        }

        return $this->contacts->delete($contact_id);
    }

}
