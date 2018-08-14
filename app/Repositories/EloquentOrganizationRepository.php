<?php
namespace TenFour\Repositories;

use TenFour\Models\Organization;
use TenFour\Models\Setting;
use TenFour\Models\User;
use TenFour\Contracts\Repositories\OrganizationRepository;
use TenFour\Contracts\Repositories\ContactRepository;
use TenFour\Contracts\Repositories\PersonRepository;
use TenFour\Services\AnalyticsService;
use DB;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Notification;
use TenFour\Notifications\PersonJoinedOrganization;
use TenFour\Notifications\PersonLeftOrganization;
use TenFour\Services\StorageService;
use TenFour\Services\CreditService;

class EloquentOrganizationRepository implements OrganizationRepository
{
    const RESTRICTED_SETTINGS = ['plan_and_credits'];

    public function __construct(StorageService $storageService, CreditService $creditService)
    {
        $this->storageService = $storageService;
        $this->creditService = $creditService;
    }

    public function all($subdomain = false, $name = false)
    {
        $query = Organization::select('organizations.id', 'organizations.name', 'subdomain', 'organizations.profile_picture');

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

    public function findByEmail($email) {
        return Organization::leftJoin('contacts', 'organizations.id', '=', 'contacts.organization_id')
            ->where('contacts.contact', '=', $email)
            ->get(['organizations.*'])
            ->toArray();
    }

    public function update(array $input, $id, $user_role = 'responder')
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
        return $this->find($id, $user_role);
    }

    public function create(array $input)
    {
        $organization = Organization::create(array_except($input, ['settings']));

        if (isset($input['settings'])) {
            $this->updateSettings($input['settings'], $organization->id);
        }

        (new AnalyticsService())->track('Organization Added', [
            'org_id'          => $organization->id,
            'subdomain'       => $organization->subdomain,
        ]);

        return $this->find($organization->id);
    }

    public function find($id, $user_role = 'responder')
    {
        $orgModel = Organization::with('settings')
            ->with('subscriptions')
            ->leftJoin('users', function ($join) {
                $join
                ->on('organizations.id', '=', 'users.organization_id')
                ->on('users.role', '=', DB::raw('\'owner\'')); // @todo Is there a better way than using raw()?
            })
            ->select('organizations.id', 'organizations.name', 'subdomain', 'organizations.profile_picture', 'users.id as user_id', 'role')
            ->findOrFail($id);

        $org = $orgModel->toArray();

        if (!($user_role === 'admin' || $user_role === 'owner')) {
            $org['settings'] = array_filter($org['settings'], function ($setting) {
                return !$setting['restricted'];
            });
        }

        $org['credits'] = $this->creditService->getBalance($id);
        $org['current_subscription'] = $orgModel->currentSubscription();
        $org['user_count'] = User::where('organization_id', '=', $id)->count();

        return $org;
    }

    public function delete($id)
    {
        $organization = Organization::findorFail($id);

        // Foreign Keys should take care of deleting members!
        // ... then delete the organization
        $organization->delete();

        (new AnalyticsService())->track('Organization Removed', [
            'org_id'          => $id,
        ]);

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

    public function setSetting($id, $key, $setting) {
        $restricted = in_array($key, self::RESTRICTED_SETTINGS);

        Setting::updateOrCreate([
            'organization_id' => $id,
            'key' => $key
        ], [
            'values' => $setting,
            'restricted' => $restricted
        ]);
    }

    protected function updateSettings(array $settings, $id)
    {
        foreach ($settings as $key => $setting)
        {
            $restricted = in_array($key, self::RESTRICTED_SETTINGS);

            Setting::updateOrCreate([
                'organization_id' => $id,
                'key' => $key
            ], [
                'values' => $setting,
                'restricted' => $restricted
            ]);
        };

        (new AnalyticsService())->track('Settings Changed', [
            'org_id'      => $id,
            'settings'    => $settings,
        ]);
    }

}
