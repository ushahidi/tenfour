<?php

namespace TenFour\Seeders;

use Illuminate\Database\Seeder;
use TenFour\Models\User;
use TenFour\Models\Group;
use TenFour\Models\Organization;

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

        $group2 = Group::create([
            'organization_id' => $organization->id,
            'name' => 'Test Group 2'
        ]);

        $this->addUsersToGroup($users, $group2);
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
