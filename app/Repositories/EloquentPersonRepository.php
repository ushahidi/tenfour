<?php
namespace TenFour\Repositories;

use TenFour\Models\Organization;
use TenFour\Models\User;
use TenFour\Models\Subscription;
use TenFour\Contracts\Repositories\PersonRepository;
use TenFour\Contracts\Repositories\ContactRepository;
use TenFour\Contracts\Repositories\CheckInRepository;
use TenFour\Contracts\Repositories\GroupRepository;
use TenFour\Http\Transformers\OrganizationTransformer;
use TenFour\Http\Transformers\UserTransformer;
use DB;
use Validator;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Notification;
use TenFour\Notifications\PersonJoinedOrganization;
use TenFour\Notifications\PersonLeftOrganization;
use Illuminate\Support\Facades\Hash;
use TenFour\Services\StorageService;
use TenFour\Services\CreditService;
use TenFour\Services\AnalyticsService;

class EloquentPersonRepository implements PersonRepository
{
    public function __construct(CheckInRepository $check_ins, ContactRepository $contacts, GroupRepository $groups, StorageService $storageService, CreditService $creditService)
    {
        $this->check_ins = $check_ins;
        $this->contacts = $contacts;
        $this->groups = $groups;
        $this->storageService = $storageService;
        $this->creditService = $creditService;
    }

    // OrgCrudRepository
    public function all($organization_id, $offset = 0, $limit = 0, $filter = null)
    {
        $query = Organization::findOrFail($organization_id)
            ->members()
            ->select('users.*','role')
            ->orderby('name', 'asc');

        if ($filter) {
            $query = $query->where(function ($query) use ($filter, $organization_id) {
                $query = $query->whereRaw( "LOWER(`name`) like ?", array( '%'.strtolower($filter).'%' ) );
                $query = $query->orwhereRaw( "LOWER(`role`) like ?", array( '%'.strtolower($filter).'%' ) );

                $query = $query->orwhereHas("contacts", function ($query) use ($filter, $organization_id) {
                  $query->whereRaw( "LOWER(`contact`) like ?", array( '%'.strtolower($filter).'%' ) );
                  $query->where('organization_id','=',$organization_id);
                });

                $query = $query->orwhereHas("groups", function ($query) use ($filter, $organization_id) {
                  $query->whereRaw( "LOWER(`name`) like ?", array( '%'.strtolower($filter).'%' ) );
                  $query->where('organization_id','=',$organization_id);
                });
            });
        } else {
            $query = $query->with('contacts');
        }

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
            $input['role'] = 'responder';
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

        (new AnalyticsService())->track('Person Added', [
            'org_id'        => $organization->id,
            'user_id'       => $user->id,
            'total_members' => $this->getOrganizationMemberCount($organization_id),
            'total_users'   => $this->getOrganizationUserCount($organization_id),
        ]);

        return $user->toArray();
    }

    private function getOrganizationMemberCount($org_id)
    {
        return User::where('organization_id', '=', $org_id)->where('person_type', '=', 'member')->count();
    }

    private function getOrganizationUserCount($org_id)
    {
        return User::where('organization_id', '=', $org_id)->where('person_type', '=', 'user')->count();
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

            // Update groups
            if (isset($input['groups'])) {
                $user->groups()->sync(collect($input['groups'])->pluck('id')->all());
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

        (new AnalyticsService())->track('Person Removed', [
            'org_id'        => $organization_id,
            'user_id'       => $user_id,
            'total_members' => $this->getOrganizationMemberCount($organization_id),
            'total_users'   => $this->getOrganizationUserCount($organization_id),
        ]);

        return $user->toArray();
    }

    public function findByEmailAndSubdomain($email, $subdomain)
    {
        return User::leftJoin('organizations', 'users.organization_id', '=', 'organizations.id')
            ->leftJoin('contacts', 'contacts.user_id', '=', 'users.id')
            ->where('organizations.subdomain', '=', $subdomain)
            ->where('contacts.contact', '=', $email)
            ->first()
            ->toArray();
    }

    public function findBySource($organization_id, $source, $source_id)
    {
        return User::where('source', '=', $source)
            ->where('source_id', '=', $source_id)
            ->where('organization_id', '=', $organization_id)
            ->first();
    }

    // OrgCrudRepository
    public function find($organization_id, $user_id, $history_offset = 0, $history_limit = 1)
    {
        $userModel = User::where('id', $user_id)
            ->where('organization_id', $organization_id)
            ->with([
                'checkins' => function ($query) use ($history_offset, $history_limit) {
                    $query->latest()->offset($history_offset)->limit($history_limit);
                },
                'contacts.replies' => function ($query) use ($history_offset, $history_limit) {
                    $query->latest()->offset($history_offset)->limit($history_limit);
                },
                'replies' => function ($query) use ($history_offset, $history_limit) {
                    $query->latest()->offset($history_offset)->limit($history_limit);
                },
                'contacts'
            ])
            // ->with('notifications')
            ->with('organization')
            ->with('groups')
            ->with('organization.subscriptions')
            ->firstOrFail();

        $user = $userModel->toArray();

        $user['has_logged_in'] = $userModel->hasLoggedIn();
        $user['organization']['credits'] = $this->creditService->getBalance($user['organization']['id']);
        $user['organization']['current_subscription'] = $userModel->organization->currentSubscription();

        $user['organization'] = (new OrganizationTransformer)->transform($user['organization']);

        foreach ($user['checkins'] as &$check_in)
        {
            foreach ($check_in['recipients'] as &$recipient)
            {
                  $recipient = (new UserTransformer)->transform($recipient);
            }
        }

        return $user;
    }

    public function findObject($id)
    {
        return User::with('contacts')
            // ->with('notifications')
            ->find($id);
    }

    public function getAdmins($organization_id)
    {
        return User::where('organization_id', $organization_id)
            ->whereIn('role', ['admin', 'owner'])
            ->get();
    }

    public function getOwner($organization_id)
    {
        return User::where('organization_id', $organization_id)
            ->where('role', '=', 'owner')
            ->first();
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
