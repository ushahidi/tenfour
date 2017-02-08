<?php
namespace RollCall\Repositories;

use RollCall\Models\Organization;
use RollCall\Models\User;
use RollCall\Contracts\Repositories\PersonRepository;
use RollCall\Contracts\Repositories\ContactRepository;
use RollCall\Contracts\Repositories\RollCallRepository;
use DB;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Notification;
use RollCall\Notifications\PersonJoinedOrganization;
use RollCall\Notifications\PersonLeftOrganization;

class EloquentPersonRepository implements PersonRepository
{
    public function __construct(RollCallRepository $roll_calls, ContactRepository $contacts)
    {
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
        $user = User::where('id', $user_id)
            ->where('organization_id', $id)
            ->firstOrFail();

        // Update user and role details
        DB::transaction(function () use ($input, $user_id, &$user) {
            if ($input['role'] == 'owner') {
                // Get current owner
                $owner = User::where('organization_id', '=', $user->organization_id)
                          ->where('role', '=', 'owner')
                          ->first();

                // ...and assign admin role before transferring ownership
                $owner->role = 'admin';
                $owner->save();
            }

            // Update user
            $user->update($input);

            // Mark notifications read
            if (isset($input['notifications'])) {
                $user->unreadNotifications->markAsRead();
            }
        });

        return $user->toArray();
    }

    public function getMember($id, $user_id)
    {
        // This should probably be passed in as param but there
        // might not be any benefit of showing a user's full
        // roll call activity here.
        $history_limit = 1;

        $userModel = User::where('id', $user_id)
            ->where('organization_id', $id)
            ->with([
                'rollcalls' => function ($query) use ($history_limit) {
                    $query->latest()->limit($history_limit);
                },
                'contacts.replies' => function ($query) use ($history_limit) {
                    $query->latest()->limit($history_limit);
                },
                'contacts'
            ])
            ->with('notifications')
            ->firstOrFail();

        $user = $userModel->toArray();

        $user['has_logged_in'] = $userModel->hasLoggedIn();

        // @todo can we remove this?
        foreach ($user['rollcalls'] as &$roll_call)
        {
            $roll_call += $this->roll_calls->getCounts($roll_call['id']);
        }

        return $user;
    }

    public function findObject($id)
    {
        return User::with('contacts')
            ->with('notifications')
            ->find($id);
    }

    public function addMember(array $input, $organization_id)
    {
        $organization = Organization::findOrFail($organization_id);

        if (!isset($input['role'])) {
            $input['role'] = 'member';
        }

        $input['organization_id'] = $organization->id;

        $user = User::create($input)->toArray();

        Notification::send($this->getAdmins($organization['id']),
            new PersonJoinedOrganization(new User($user)));

        return $user;
    }

    public function getMembers($organization_id)
    {
        return Organization::findOrFail($organization_id)
            ->members()
            ->with('contacts')
            ->select('users.*','role')
            ->orderby('name', 'asc')
            ->get()
            ->toArray();
    }

    protected function getAdmins($organization_id)
    {
        return User::where('organization_id', $organization_id)
            ->whereIn('role', ['admin', 'owner'])
            ->get();
    }

    public function deleteMember($organization_id, $user_id)
    {
        $user = User::where('id', $user_id)
            ->where('organization_id', $organization_id)
            ->firstOrFail();

        $user->delete();

        Notification::send($this->getAdmins($user->organization_id),
            new PersonLeftOrganization($user));

        return $user->toArray();
    }

    public function getMemberRole($organization_id, $user_id)
    {
        $role = User::where('organization_id', '=', $organization_id)
              ->where('id', '=', $user_id)
              ->value('role');

        return $role;
    }

    public function isMember($user_id, $org_id)
    {
        return (bool) User::where('id', $user_id)
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

    public function addContact(array $input, $organization_id, $user_id)
    {
        $user = User::where('id', $user_id)
            ->where('organization_id', $organization_id)
            ->firstOrFail();

        $input['can_receive'] = 1;
        $input['user_id'] = $user->id;

        return $this->contacts->create($input);
    }

    public function updateContact(array $input, $organization_id, $user_id, $contact_id)
    {
        $user = User::where('id', $user_id)
            ->where('organization_id', $organization_id)
            ->firstOrFail();

        // @todo ensure contact belongs to user!
        return $this->contacts->update($input, $contact_id);
    }

    public function deleteContact($organization_id, $user_id, $contact_id)
    {
        $user = User::where('id', $user_id)
            ->where('organization_id', $organization_id)
            ->firstOrFail();

        // @todo ensure contact belongs to user!
        return $this->contacts->delete($contact_id);
    }

}
