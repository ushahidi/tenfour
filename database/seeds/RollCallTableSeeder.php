<?php
namespace RollCall\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Notification;

use RollCall\Models\Organization;
use RollCall\Models\User;
use RollCall\Models\Contact;
use RollCall\Models\RollCall;
use RollCall\Models\Reply;
use RollCall\Notifications\RollCallReceived;
use RollCall\Notifications\ReplyReceived;

class RollCallTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::select('id')->get();

        // Grab seeded organization
        $organization = Organization::where('name', 'Ushahidi')
                      ->select('id')
                      ->firstOrFail();

        $rollCall = RollCall::firstOrCreate([
            'organization_id' => $organization->id,
            'user_id' => $users[0]->id
        ]);

        $rollCall->update([
            'message' => 'Test rollcall',
            'answers' => ['No', 'Yes']
        ]);

        // Add recipients
        $recipients = [];

        foreach ($users as $user) {
            array_push($recipients, $user->id);
        }

        $rollCall->recipients()->sync($recipients, false);

        Notification::send($rollCall->recipients, new RollCallReceived($rollCall));

        // Add replies
        $no_of_replies = 1;
        $reply_count = 0;

        foreach($users as $user) {
            if ($reply_count === $no_of_replies) {
                break;
            }

            $reply = Reply::firstOrCreate([
                'message'      => 'I am OK',
                'answer'       => 'Yes',
                'user_id'      => $user->id,
                'roll_call_id' => $rollCall->id,
            ]);

            Notification::send($rollCall->recipients, new ReplyReceived($reply));

            $reply_count++;
        }
    }
}
