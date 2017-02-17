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
            'password' => 'westgate',
            'person_type' => 'user',
            'first_time_login' => 0,
        ]);

        Contact::firstOrCreate([
            'type'        => 'email',
            'contact'     => 'rollcall@ushahidi.com',
            'preferred'   => 1,
            'user_id'     => $user->id,
            'subscribed'  => 1,
            'unsubscribe_token' => 'testtoken',
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
            'password' => 'waitaktri',
            'person_type' => 'user'
        ]);

        Contact::firstOrCreate([
            'type'        => 'email',
            'contact'     => 'waitaktri@ushahidi.com',
            'preferred'   => 1,
            'user_id'     => $user2->id,
            'subscribed'  => 1,
            'unsubscribe_token' => 'testtoken',
        ]);

        $user2->organizations()->sync([
                $triClub->id => [
                    'user_id' => $user2->id, 'role' => 'owner'
                ]
            ]);
    }
}
