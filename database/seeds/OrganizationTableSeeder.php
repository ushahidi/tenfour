<?php
namespace RollCall\Seeders;

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
            'url' => 'ushahidi',
        ]);

        $user->organizations()->sync([
                $organization->id =>[
                    'user_id' => $user->id, 'role' => 'owner'
                ]
            ]);

        // Second test org: Waitak Tri Club
        $triClub = Organization::firstOrCreate(
            ['name' => 'Waitakere Tri Club']
        );
        $triClub->update([
            'url' => 'waitaktri',
        ]);

        $user2 = User::firstOrCreate([
            'email' => 'robbie+tri@ushahidi.com',
            'name' => 'Robbie',
            'password' => 'waitakere'
        ]);

        $user2->organizations()->sync([
                $triClub->id => [
                    'user_id' => $user2->id, 'role' => 'owner'
                ]
            ]);
    }
}
