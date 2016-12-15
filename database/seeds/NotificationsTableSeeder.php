<?php
namespace RollCall\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use RollCall\Models\User;
use RollCall\Models\Organization;
use RollCall\Notifications\PersonJoinedOrganization;
use Illuminate\Support\Facades\Notification;

class NotificationsTableSeeder extends Seeder
{
    public function run() {
        $person = User::firstOrCreate(
            ['email' => 'dmcnamara+test@ushahidi.com']
        );

        $organization = Organization::firstOrCreate(
            ['name' => 'Ushahidi']
        );

        Notification::send($organization->members, new PersonJoinedOrganization($person, $organization));
    }
}
