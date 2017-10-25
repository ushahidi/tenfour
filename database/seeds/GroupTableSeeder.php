<?php

namespace RollCall\Seeders;

use Illuminate\Database\Seeder;
use RollCall\Models\User;
use RollCall\Models\Group;
use RollCall\Models\Organization;

class GroupTableSeeder extends Seeder
{
    protected function addUsersToGroup($users, $group) {
        $members = [];

        foreach ($users as $user) {
            array_push($members, $user->id);
        }

        $group->members()->sync($members, false);
    }

    protected function addGroups($organization, $users) 
    {
        $group = Group::create([
            'organization_id' => $organization->id,
            'name' => 'Test Group 1'
        ]);

        $this->addUsersToGroup($users, $group);

        return $group;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $organization = Organization::where('name', 'Ushahidi')
                      ->select('id')
                      ->firstOrFail();

        $users = User::select('id')->where('organization_id', $organization->id)->limit(10)->get();

        $this->addGroups($organization, $users);
    }
}
