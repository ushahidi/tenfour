<?php
namespace RollCall\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use RollCall\Models\User;


class UserTableSeeder extends Seeder
{
    public function run() {
        $user = User::firstOrCreate([
            'name' => 'Team Ushahidi',
            'email' => 'team@ushahidi.com',
        ]);

        $user->update([
            'password' => 'westgate'
        ]);
    }
}
