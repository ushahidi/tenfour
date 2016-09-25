<?php
use Illuminate\Database\Seeder;
use RollCall\Models\User;

class OrganizationTableSeeder extends Seeder
{
    // Create user that owns Ushahidi organization
    public function run() {
        $user = User::firstOrCreate([
            'username' => 'ushahidi',
            'name' => 'ushahidi',
            'email' => 'rollcall@ushahidi.com',
        ]);

        $user->update([
            'password' => 'westgate'
        ]);

        // Create Ushahidi organization
        $organization_id = DB::table('organizations')->insertGetId([
            'name' => 'Ushahidi',
            'url' => 'ushahidi.rollcall.io',
        ]);

        DB::table('organization_user')->insert([
            'organization_id' => $organization_id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    }
}
