<?php
use Illuminate\Database\Seeder;
use RollCall\Models\User;
use RollCall\Models\Organization;

class OrganizationTableSeeder extends Seeder
{
    // Create user that owns Ushahidi organization
    public function run() {

        $user = User::firstOrCreate(
            ['email' => 'rollcall@ushahidi.com']
        );

        $user->update([
            'name' => 'ushahidi',
            'password' => 'westgate'
        ]);

        $organization = Organization::firstOrCreate(
            ['name' => 'Ushahidi']
        );

        $organization->update([
            'url' => 'ushahidi.rollcall.io',
        ]);

        $user->organizations()->sync([
                $organization->id =>[
                    'user_id' => $user->id, 'role' => 'owner'
                ]
            ]);
    }
}
