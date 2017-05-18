<?php
namespace RollCall\Repositories;

use RollCall\Models\Organization;
use RollCall\Models\Setting;
use RollCall\Contracts\Repositories\OrganizationRepository;
use RollCall\Contracts\Repositories\ContactRepository;
use RollCall\Contracts\Repositories\PersonRepository;
use DB;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Notification;
use RollCall\Notifications\PersonJoinedOrganization;
use RollCall\Notifications\PersonLeftOrganization;
use RollCall\Services\StorageService;
use RollCall\Services\CreditService;

class EloquentOrganizationRepository implements OrganizationRepository
{
    protected $currentUserId = NULL;

    public function __construct(StorageService $storageService, CreditService $creditService)
    {
        $this->storageService = $storageService;
        $this->creditService = $creditService;
    }

    public function setCurrentUserId($currentUserId)
    {
        $this->currentUserId = $currentUserId;
    }

    public function all($subdomain = false, $name = false)
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

        // Filter by name
        if ($name) {
            $query->where('name', $name);
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
            $this->updateSettings($input['settings'], $organization->id);
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
        $organization = Organization::create(array_except($input, ['settings']));

        if (isset($input['settings'])) {
            $this->updateSettings($input['settings'], $organization->id);
        }

        return $this->find($organization->id);
    }

    public function find($id)
    {
        $org = Organization::with('settings')
            ->leftJoin('users', function ($join) {
                $join
                ->on('organizations.id', '=', 'users.organization_id')
                ->on('users.role', '=', DB::raw('\'owner\'')); // @todo Is there a better way than using raw()?
            })
            ->select('organizations.id', 'organizations.name', 'subdomain', 'organizations.profile_picture', 'users.id as user_id', 'role')
            ->findOrFail($id)
            ->toArray();

        $org['credits'] = $this->creditService->getBalance($id);

        return $org;
    }

    public function delete($id)
    {
        $organization = Organization::findorFail($id);

        // Foreign Keys should take care of deleting members!

        // ... then delete the organization
        $organization->delete();
        return $organization->toArray();
    }

    public function getSetting($id, $key) {
        $setting = Organization::leftJoin('settings', 'organizations.id', '=', 'settings.organization_id')
                    ->select('settings.key', 'settings.values')
                    ->where('settings.key', $key)
                    ->find($id);

        if ($setting) {
            return json_decode($setting->toArray()['values']);
        } else {
            return [];
        }

    }

    protected function updateSettings(array $settings, $id)
    {
        foreach ($settings as $key => $setting)
        {
            Setting::updateOrCreate([
                'organization_id' => $id,
                'key' => $key
            ], [
                'values' => $setting
            ]);
        };
    }

}
