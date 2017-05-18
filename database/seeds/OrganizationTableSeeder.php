<?php
namespace RollCall\Seeders;

use Illuminate\Database\Seeder;
use RollCall\Models\User;
use RollCall\Models\Organization;
use RollCall\Models\Contact;
use RollCall\Models\CreditAdjustment;

class OrganizationTableSeeder extends Seeder
{
    // Create user that owns Ushahidi organization
    public function run() {
        $organization = Organization::firstOrCreate(
            ['name' => 'Ushahidi']
        );

        CreditAdjustment::firstOrCreate([
            'organization_id' => $organization->id,
            'adjustment' => 240,
            'balance' => 240,
            'type' => 'init'
        ]);

        $organization->update([
            'subdomain' => 'ushahidi',
            'profile_picture' => 'http://github.ushahidi.org/rollcall-pattern-library/assets/img/avatar-org.png',
        ]);

        $user = User::firstOrCreate(
            ['name' => 'ushahidi']
        );

        $user->update([
            'password' => 'westgate',
            'person_type' => 'user',
            'organization_id' => $organization->id,
            'role' => 'owner',
			      'first_time_login' => 0,
        ]);

        Contact::firstOrCreate([
            'type'        => 'email',
            'contact'     => 'rollcall@ushahidi.com',
            'preferred'   => 1,
            'user_id'     => $user->id,
            'organization_id' => $organization->id,
            'unsubscribe_token' => 'testtoken',
        ]);

        // Second test org: Waitak Tri Club
        $triClub = Organization::firstOrCreate(
            ['name' => 'Waitakere Tri Club']
        );

        $triClub->update([
            'subdomain' => 'waitaktri',
        ]);

        CreditAdjustment::firstOrCreate([
            'organization_id' => $triClub->id,
            'adjustment' => 0,
            'balance' => 0,
            'type' => 'init'
        ]);

        $user2 = User::firstOrCreate([
            'name' => 'Robbie',
            'password' => 'waitaktri',
            'person_type' => 'user',
            'organization_id' => $triClub->id,
            'role' => 'owner'
        ]);

        Contact::firstOrCreate([
            'type'        => 'email',
            'contact'     => 'waitaktri@ushahidi.com',
            'preferred'   => 1,
            'user_id'     => $user2->id,
            'organization_id' => $triClub->id,
            'unsubscribe_token' => 'testtoken',
        ]);
    }
}
