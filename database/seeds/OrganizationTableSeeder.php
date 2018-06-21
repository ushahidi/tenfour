<?php
namespace TenFour\Seeders;

use Illuminate\Database\Seeder;
use TenFour\Models\User;
use TenFour\Models\Organization;
use TenFour\Models\Contact;
use TenFour\Models\CreditAdjustment;
use TenFour\Models\Subscription;

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

        Subscription::firstOrCreate([
            'organization_id' => $organization->id,
            'subscription_id' => 'test_subscription',
            'customer_id' => 'test_customer',
            'status' => 'active',
            'plan_id' => 'pro-plan',
            'quantity' => 40,
            'card_type' => 'Visa',
            'last_four' => '1111',
            'trial_ends_at' => '2016-10-30 12:05:01',
            'next_billing_at' => '2026-10-30 12:05:01',
        ]);

        $organization->update([
            'subdomain' => 'ushahidi',
            'profile_picture' => 'http://github.ushahidi.org/tenfour-pattern-library/assets/img/avatar-org.png',
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
            'contact'     => 'tenfour@ushahidi.com',
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
