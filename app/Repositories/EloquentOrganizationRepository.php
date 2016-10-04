<?php
namespace RollCall\Repositories;

use RollCall\Models\Organization;
use RollCall\Contracts\Repositories\OrganizationRepository;
use DB;

class EloquentOrganizationRepository implements OrganizationRepository
{
    public function all()
    {
        // List Organizations and their owners
        $organizations = $this->getOrganizations()
                       ->where('role', '=', 'owner')
                       ->get()
                       ->toArray();

        return $this->formatListing($organizations);
    }

    public function filterByUserId($user_id)
    {
        $organizations = $this->getOrganizations()
                       ->where('user_id', '=', $user_id)
                       ->get()
                       ->toArray();

        return $this->formatListing($organizations);
    }

    public function update(array $input, $id)
    {
        $organization = Organization::findorFail($id);

        // Get org input
        $org_input = array_except($input, ['members']);

        // Get members
        $members = array_get($input, 'members');

        DB::transaction(function () use ($org_input, $members, $organization) {
            $organization->update($org_input);

            foreach($members as $member)
            {
                if ($member['role'] === 'owner') {
                    // Get current owner
                    $owner_id = DB::table('organization_user')
                      ->where('organization_id', '=', $organization->id)
                      ->where('role', '=', 'owner')
                      ->value('user_id');

                    // ...and assign member role before transferring ownership
                    $organization->users()->updateExistingPivot($owner_id, ['role' => 'member']);
                }

                $organization->users()->updateExistingPivot($member['id'], ['role' => $member['role']]);
            }
        });

        return $organization->toArray() +
        [
            'members' => $members
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
            $organization->users()->attach($owner_id, ['role' => 'owner']);
        });

        return $organization->toArray() +
        [
            'members' => [
                [
                    'id' => $owner_id,
                    'role' => 'owner'
                ]
            ]
        ];
    }

    public function find($id)
    {
        // Get organization + owner
        $organization = Organization::join('organization_user', 'organizations.id', '=', 'organization_id')
                      ->select('organizations.*', 'user_id', 'role')
                      ->where('organizations.id', '=', $id)
                      ->where('role', '=', 'owner')
                      ->first()
                      ->toArray();

        $organization['members'] = [
            [
                'id'   => $organization['user_id'],
                'role' => $organization['role']
            ]
        ];

        unset($organization['role']);
        unset($organization['user_id']);

        return $organization;
    }

    public function delete($id)
    {
        $organization = Organization::findorFail($id);

        // Delete all members
        $organization->users()->detach();

        // ... then delete the organization
        $organization->delete();
        return $organization->toArray();
    }

    public function addMembers(array $input, $id)
    {
        $organization = Organization::findorFail($id);
        $members = [];
        $ids = [];

        if (is_array(head($input))) {
            foreach($input as &$member)
            {
                // Assign default 'member' role if unspecified
                if (!isset($member['role'])) {
                    $member['role'] = 'member';
                }

                $ids[$member['id']] = ['role' => $member['role']];
            }

            // Add members to response
            $members = $input;
        } else {
            if(!isset($input['role'])) {
                $input['role'] = 'member';
            }

            $ids[$input['id']] = ['role' => $input['role']];

            // Add contact to response
            $members = [$input];
        }

        $organization->users()->attach($ids);

        return $organization->toArray() +
        [
            'members' => $members
        ];
    }

    public function listMembers($id)
    {
        $organization = Organization::
                      with(['users' => function($query) {
                          $query->select('name');
                      }])
                      ->findOrFail($id);

        $members = $organization
                 ->users
                 ->toArray();

        foreach($members as &$member)
        {
            $member = [
                'id'   => $member['pivot']['user_id'],
                'name' => $member['name'],
                'role' => $member['pivot']['role'],
            ];
        }

        return $organization->toArray() +
        [
            'members' => $members
        ];
    }

    public function deleteMember($id, $user_id)
    {
        $organization = Organization::findOrFail($id);

        $member = $organization->users()
                ->select('name')
                ->findOrFail($user_id);

        $organization->users()->detach($user_id);

        return $organization->toArray() +
        [
            'members' => [
                [
                    'id'   => $member->pivot->user_id,
                    'role' => $member->pivot->role,
                    'name' => $member->name
                ]
            ]
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
            ->where('organization_id', $id)
            ->count();
    }

    protected function formatListing($organizations)
    {
        // Show organization owner/ member in listing
        foreach($organizations as &$organization)
        {
            $organization['members'] = [
                [
                    'id'   => $organization['user_id'],
                    'role' => $organization['role']
                ]
            ];

            unset($organization['role']);
            unset($organization['user_id']);
        }

        return $organizations;
    }

    protected function getOrganizations()
    {
        return Organization::join('organization_user', 'organizations.id', '=', 'organization_id')
                       ->select('organizations.*', 'user_id', 'role');
    }
}
