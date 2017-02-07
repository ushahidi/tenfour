<?php
namespace RollCall\Repositories;

use RollCall\Models\Organization;
use RollCall\Models\Setting;
use RollCall\Contracts\Repositories\OrganizationRepository;
use DB;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Notification;
use RollCall\Notifications\PersonJoinedOrganization;
use RollCall\Notifications\PersonLeftOrganization;

class EloquentOrganizationRepository implements OrganizationRepository
{
    protected $currentUserId = NULL;

    public function __construct()
    {
    }

    public function setCurrentUserId($currentUserId)
    {
        $this->currentUserId = $currentUserId;
    }

    public function all($subdomain = false)
    {
        $query = Organization::select('organizations.id', 'name', 'subdomain');

        // If we're authenticated, just return orgs we're a member of
        if ($this->currentUserId) {
            $query->leftJoin('organization_user', 'organizations.id', '=', 'organization_user.organization_id');
            $query->select('organizations.id', 'name', 'subdomain', 'user_id', 'role');
            $query->where('organization_user.user_id', $this->currentUserId);
        }

        // Filter by subdomain
        if ($subdomain) {
            $query->where('subdomain', $subdomain);
        }

        return $query->get()->toArray();
    }

    public function update(array $input, $id)
    {
        $organization = Organization::findorFail($id);
        $organization->update($input);

        if (isset($input['settings'])) {
          foreach ($input['settings'] as $key => $setting) {
            Setting::updateOrCreate([
              'organization_id' => $organization->id,
              'key' => $key
            ], [
              'values' => $setting
            ]);
          };
        }

        return $this->find($id);
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
        return Organization::with('settings')
            ->leftJoin('organization_user', 'organizations.id', '=', 'organization_user.organization_id')
            ->select('organizations.id', 'name', 'subdomain', 'user_id', 'role')
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

    // public function getAdmins($id)
    // {
    //     return Organization::findOrFail($id)
    //         ->members()
    //         ->select('users.*','role')
    //         ->whereIn('role', ['admin', 'owner'])
    //         ->get();
    // }

}
