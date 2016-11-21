<?php
namespace RollCall\Repositories;

use RollCall\Models\Organization;
use RollCall\Models\User;
use RollCall\Contracts\Repositories\ContactRepository;
use RollCall\Contracts\Repositories\UserRepository;
use RollCall\Contracts\Repositories\OrganizationRepository;
use RollCall\Contracts\Repositories\RollCallRepository;
use DB;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class EloquentOrganizationRepository implements OrganizationRepository
{
    public function __construct(ContactRepository $contacts, UserRepository $users, RollCallRepository $roll_calls)
    {
        $this->contacts = $contacts;
        $this->users = $users;
        $this->roll_calls = $roll_calls;
    }

    public function all()
    {
        return Organization::leftJoin('organization_user', 'organizations.id', '=', 'organization_user.organization_id')
            ->select('organizations.id', 'name', 'url', 'user_id', 'role')
            ->where('organization_user.role', 'owner')
            ->get()
            ->toArray();
    }

    public function filterByUserId($user_id)
    {
        return Organization::leftJoin('organization_user', 'organizations.id', '=', 'organization_user.organization_id')
            ->select('organizations.id', 'name', 'url', 'user_id', 'role')
            ->where('organization_user.user_id', $user_id)
            ->get()
            ->toArray();
    }

    public function update(array $input, $id)
    {
        $organization = Organization::findorFail($id);
        $organization->update($input);

        return $organization->toArray();
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

    public function create(array $input)
    {
        $organization = null;

        // Get organization params
        $org_input = array_except($input, ['user_id']);

        // Get owner id
        $owner_id = array_only($input, ['user_id'])['user_id'];

        DB::transaction(function () use ($org_input, $owner_id, &$organization) {
            $organization = Organization::create($org_input);

            // Assign 'owner' role to the user associated
            // with the organization when it's created
            $organization->members()->attach($owner_id, ['role' => 'owner']);
        });

        return $organization->toArray() +
        [
            'user_id' => $owner_id,
            'role'    => 'owner'
        ];
    }

    public function find($id)
    {
        return Organization::leftJoin('organization_user', 'organizations.id', '=', 'organization_user.organization_id')
            ->select('organizations.id', 'name', 'url', 'user_id', 'role')
            ->where('role', 'owner')
            ->findOrFail($id)
            ->toArray();
    }

    public function delete($id)
    {
        $organization = Organization::findorFail($id);

        // Delete all members
        $organization->members()->detach();

        // ... then delete the organization
        $organization->delete();
        return $organization->toArray();
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

        $user = User::with([
            'rollcalls' => function ($query) use ($history_limit) {
                $query->limit($history_limit);
            },
            'contacts.replies' => function ($query) use ($history_limit) {
                $query->limit($history_limit);
            }
        ])
              ->find($user_id)
              ->toArray() + [
                  'role' => $role
              ];

        foreach ($user['rollcalls'] as &$roll_call)
        {
            $roll_call = $this->roll_calls->addCounts($roll_call);
        }

        return $user;
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

    public function addMember(array $input, $id)
    {
        $organization = Organization::findorFail($id);

        $user = null;

        if (!isset($input['role'])) {
            $input['role'] = 'member';
        }

        DB::transaction(function () use (&$user, $input, $organization) {
            $user_input = array_except($input, ['email', 'role']);
            $email = array_only($input, ['email'])['email'];

            $user_id = User::firstOrCreate(['email' => $email])->id;

            $user = $this->users->update($user_input, $user_id);

            $organization->members()->attach($user_id, ['role' => $input['role']]);
        });

        return $user + [
            'role' => $input['role']
        ];
    }

    public function getMembers($id)
    {
        return Organization::with([
            'members' => function ($query) {
                $query->select('users.id', 'name', 'users.email', 'role');
        }])
            ->findOrFail($id)
            ->toArray();
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
}
