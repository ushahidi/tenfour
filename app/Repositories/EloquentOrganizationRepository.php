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

        $members = array_get($input, 'members');

        $ids = [];

        foreach($members as &$member)
        {
            // Assign default 'member' role if unspecified
            if (!isset($member['role'])) {
                $member['role'] = 'member';
            }

            $ids[$member['id']] = ['role' => $member['role']];
        }

        $organization->users()->attach($ids);

        return $organization->toArray() +
        [
            'members' => $members
        ];
    }

    public function deleteMembers(array $input, $id)
    {
        $organization = Organization::findorFail($id);

        $members = array_get($input, 'members');

        $ids = [];

        foreach($members as $member)
        {
            array_push($ids, $member['id']);
        }

        $organization->users()->detach($ids);

        return $organization->toArray() +
        [
            'members' => $members
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
