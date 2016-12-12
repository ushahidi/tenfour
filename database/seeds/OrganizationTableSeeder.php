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
            'url' => 'ushahidi.rollcall.io',
        ]);

        $user->organizations()->sync([
                $organization->id =>[
                    'user_id' => $user->id, 'role' => 'owner'
                ]
            ]);
    }
}
