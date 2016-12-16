<?php
namespace RollCall\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use RollCall\Models\User;
use RollCall\Models\Organization;
use RollCall\Notifications\PersonJoinedOrganization;
use RollCall\Notifications\PersonLeftOrganization;
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

        $user = User::firstOrCreate(
            ['email' => 'rollcall@ushahidi.com']
        );

        Notification::send($organization->members, new PersonJoinedOrganization($person));

        $user->unreadNotifications->markAsRead();

        Notification::send($organization->members, new PersonLeftOrganization($person));

    }
}
