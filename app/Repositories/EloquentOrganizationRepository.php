<?php
namespace RollCall\Repositories;

use RollCall\Models\Organization;
use RollCall\Models\User;
use RollCall\Models\Contact;
use RollCall\Contracts\Repositories\OrganizationRepository;
use DB;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class EloquentOrganizationRepository implements OrganizationRepository
{
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
        $organization = Organization::findorFail($id);

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

        return $organization->toArray() +
        [
            'members' => [
                [
                    'id'   => $user_id,
                    'role' => $input['role']
                ]
            ]
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
            'members' => [
                [
                    'id'   => $owner_id,
                    'role' => 'owner'
                ]
            ]
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

    public function getMemberContacts($id, $user_id)
    {
        $organization = Organization::with([
            'members' => function ($query) use ($user_id) {
                $query->select('users.id')->where('users.id', $user_id);
            }])->findOrFail($id);

        if ($organization->members->isEmpty()) {
            throw (new ModelNotFoundException)->setModel('User');
        }

        $contacts  = Contact::where('user_id', $user_id)->get();

        if ($contacts->isEmpty()) {
            throw (new ModelNotFoundException)->setModel('Contact');
        }

        $organization = $organization->toArray();
        $organization['members'][0]['contacts'] = $contacts->toArray();

        return $organization;
    }

    public function addContacts(array $input, $id, $user_id)
    {
        $organization = Organization::with([
            'members' => function ($query) use ($user_id) {
                $query->select('users.id')->where('users.id', $user_id);
            }])->findOrFail($id);

        if ($organization->members->isEmpty()) {
            throw (new ModelNotFoundException)->setModel('User');
        }

        $contacts = [];

        if (is_array(head($input))) {
            DB::transaction(function () use ($input, &$contacts, $user_id) {
                foreach($input as $contact)
                {
                    array_push($contacts, $this->addContact($contact, $user_id));
                }
            });
        } else {
            array_push($contacts, $this->addContact($input, $user_id));
        }

        $organization = $organization->toArray();
        $organization['members'][0]['contacts'] = $contacts;

        return $organization;
    }

    public function addMembers(array $input, $id)
    {
        $organization = Organization::findorFail($id);
        $members = [];
        $ids = [];

        if (! is_array(head($input))) {
            $input = [$input];
        }

        foreach($input as &$member)
        {
            $member = array_only($member, ['email']);

            // Create user with email as username
            $user = User::firstOrCreate([
                    'email'    => $member['email'],
                    'username' => $member['email'],
            ]);

            // Assign default 'member' role if unspecified
            if (!isset($member['role'])) {
                $member['role'] = 'member';
            }

            $ids[$user->id] = [
                'role' => $member['role']
            ];

            $member['id'] = $user->id;
        }

        DB::transaction(function () use ($organization, $ids) {
            $organization->members()->sync($ids, false);
        });

        return $organization->toArray() +
        [
            'members' => $input
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

        $organization->members()->detach($organization->members->first()->id);

        return $organization->toArray();
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

    protected function addContact($input, $user_id)
    {
        $input['can_receive'] = 1;
        $input['user_id'] = $user_id;

        return Contact::create($input)->toArray();
    }
}
