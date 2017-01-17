<?php
namespace RollCall\Seeders;

use Illuminate\Database\Seeder;
use RollCall\Models\User;
use RollCall\Models\Organization;
use RollCall\Models\Contact;

class OrganizationTableSeeder extends Seeder
{
    // Create user that owns Ushahidi organization
    public function run() {

        $user = User::firstOrCreate(
            ['name' => 'ushahidi']
        );

        $user->update([
            'password' => 'westgate'
        ]);

        Contact::firstOrCreate([
            'type'        => 'email',
            'contact'     => 'rollcall@ushahidi.com',
            'can_receive' => 1,
            'user_id'     => $user->id
        ]);

        $organization = Organization::firstOrCreate(
            ['name' => 'Ushahidi']
        );

        $organization->update([
            'subdomain' => 'ushahidi',
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
            'subdomain' => 'waitaktri',
        ]);

        $user2 = User::firstOrCreate([
            'name' => 'Robbie',
            'password' => 'waitaktri'
        ]);

        Contact::firstOrCreate([
            'type'        => 'email',
            'contact'     => 'waitaktri@ushahidi.com',
            'can_receive' => 1,
            'user_id'     => $user2->id
        ]);

        $user2->organizations()->sync([
                $triClub->id => [
                    'user_id' => $user2->id, 'role' => 'owner'
                ]
            ]);
    }
}
