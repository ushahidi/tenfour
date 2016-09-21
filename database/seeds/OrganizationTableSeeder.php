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

        // Add OAuth token for Ushahidi organization owner
        $session_id = DB::table('oauth_sessions')->insertGetId([
            'client_id' => 'webapp',
            'owner_type' => 'user',
            'owner_id' => $user->id,
        ]);

        DB::table('oauth_access_tokens')->insert([
            'id' => 'ushahidiownertoken',
            'session_id' => $session_id,
            'expire_time' => '1856429714',
        ]);

        DB::table('oauth_access_token_scopes')->insert([
            'access_token_id' => 'ushahidiownertoken',
            'scope_id' => 'organization',
        ]);

        DB::table('oauth_session_scopes')->insert([
            'session_id' => $session_id,
            'scope_id' => 'organization',
        ]);
    }
}
