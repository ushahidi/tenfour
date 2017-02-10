<?php
namespace RollCall\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use RollCall\Models\User;
use RollCall\Models\Contact;

class UserTableSeeder extends Seeder
{
    public function run() {
        $user = User::firstOrCreate([
            'name' => 'Team Ushahidi'
        ]);

        Contact::firstOrCreate([
            'type'        => 'email',
            'contact'     => 'team@ushahidi.com',
            'can_receive' => 1,
            'user_id'     => $user->id
        ]);

        $user->update([
            'password' => 'westgate',
            'person_type' => 'user'
        ]);
    }
}
