<?php
namespace RollCall\Seeders;

use Illuminate\Database\Seeder;

use RollCall\Models\Organization;
use RollCall\Models\User;
use RollCall\Models\Contact;
use RollCall\Models\RollCall;
use RollCall\Models\Reply;

class RollCallTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::where('username', 'charlie')
               ->orwhere('username', 'linda')
               ->select('id', 'email')
               ->get();

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
        ]);

        // Add recipients
        $recipients = [];

        foreach ($users as $user) {
            $contact = Contact::firstOrCreate([
                'user_id'     => $user->id,
                'can_receive' => 1,
                'type'        => 'email',
                'contact'     => $user->email
            ]);

            array_push($recipients, $user->id);
        }

        $rollCall->recipients()->sync($recipients, false);

        // Add replies
        $no_of_replies = 1;
        $reply_count = 0;

        foreach($users as $user) {
            if ($reply_count === $no_of_replies) {
                break;
            }

            Reply::firstOrCreate([
                'message'      => 'I am OK',
                'user_id'      => $user->id,
                'roll_call_id' => $rollCall->id,
            ]);

            $reply_count++;
        }
    }
}
