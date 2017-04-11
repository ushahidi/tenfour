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
use RollCall\Services\StorageService;

class EloquentOrganizationRepository implements OrganizationRepository
{
    protected $currentUserId = NULL;

    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function setCurrentUserId($currentUserId)
    {
        $this->currentUserId = $currentUserId;
    }

    public function all($subdomain = false)
    {
        $query = Organization::select('organizations.id', 'organizations.name', 'subdomain', 'organizations.profile_picture');

        // If we're authenticated, just return orgs we're a member of
        if ($this->currentUserId) {
            $query->join('users', 'organizations.id', '=', 'users.organization_id');
            $query->select('organizations.id', 'organizations.name', 'subdomain', 'organizations.profile_picture', 'users.id as user_id', 'role');
            $query->where('users.id', $this->currentUserId);
        }

        // Filter by subdomain
        if ($subdomain) {
            $query->where('subdomain', $subdomain);
        }

        return $query->get()->toArray();
    }

    public function findBySubdomain($subdomain)
    {
        return Organization::where('subdomain', $subdomain)
            ->firstOrFail()
            ->toArray();
    }

    public function update(array $input, $id)
    {
        $organization = Organization::findorFail($id);

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

        if (isset($input['_input_image'])) {
            $file = $input['_input_image'];
            $input['profile_picture'] = $this->storageService->storeBase64File($file, uniqid($organization->id), 'orgavatar');
            unset($input['_input_image']);
        }

        $organization->update($input);
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
            // FIXME: need to add user
            //$organization->members()->attach($owner_id, ['role' => 'owner']);
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
            ->leftJoin('users', function ($join) {
                $join
                ->on('organizations.id', '=', 'users.organization_id')
                ->on('users.role', '=', DB::raw('\'owner\'')); // @todo Is there a better way than using raw()?
            })
            ->select('organizations.id', 'organizations.name', 'subdomain', 'organizations.profile_picture', 'users.id as user_id', 'role')
            ->findOrFail($id)
            ->toArray();
    }

    public function delete($id)
    {
        $organization = Organization::findorFail($id);

        // Foreign Keys should take care of deleting members!

        // ... then delete the organization
        $organization->delete();
        return $organization->toArray();
    }

}
