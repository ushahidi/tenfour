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
use DB;

class RollCallTableSeeder extends Seeder
{
    protected function addUsersToRollCall($users, $rollCall) {
        $recipients = [];

        foreach ($users as $user) {
            array_push($recipients, $user->id);
        }

        $rollCall->recipients()->sync($recipients, false);
    }

    protected function addRollCalls($organization, $users, $answers) {
      foreach ($users as $user) {
          $rollCall = RollCall::firstOrCreate([
              'organization_id' => $organization->id,
              'user_id' => $user->id
          ]);

          $rollCall->update([
              'message' => 'Test rollcall',
              'answers' => $answers
          ]);

          $this->addUsersToRollCall($users, $rollCall);

          Notification::send($rollCall->recipients, new RollCallReceived($rollCall));

          // Add replies
          $no_of_replies = 2;
          $reply_count = 0;
          $message = 'I am OK';

          foreach ($users as $user) {
              if ($reply_count === $no_of_replies) {
                  break;
              }

              $reply = Reply::firstOrCreate([
                  'message'      => $message,
                  'answer'       => $answers[$reply_count]['answer'],
                  'user_id'      => $user->id,
                  'roll_call_id' => $rollCall->id,
              ]);

              DB::table('roll_call_recipients')
                  ->where('roll_call_id', '=', $rollCall->id)
                  ->where('user_id', '=', $user->id)
                  ->update(['response_status' => 'replied']);

              $message = 'I am not OK';

              Notification::send($rollCall->recipients, new ReplyReceived($reply));

              $reply_count++;
          }
        }

        return $rollCall;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $organization = Organization::where('name', 'Ushahidi')
                      ->select('id')
                      ->firstOrFail();

        $users = User::select('id')->where('organization_id', $organization->id)->limit(20)->get();

        // default answers

        $this->addRollCalls($organization, $users, [
            ['answer'=>'No','color'=>'#BC6969','icon'=>'icon-exclaim','custom'=>false],
            ['answer'=>'Yes','color'=>'#E8C440','icon'=>'icon-check','custom'=>false]
        ]);

        // custom answers

        $rollCall = $this->addRollCalls($organization, [$users[0], $users[1]], [
          ['answer'=>'Custom answer blue','color'=>'#2274B4','icon'=>'icon-exclaim','custom'=>true],
          ['answer'=>'Custom answer teal','color'=>'#4CBFCE','icon'=>'icon-check','custom'=>true]
        ]);

        Reply::firstOrCreate([
            'message'      => 'I am other',
            'answer'       => 'Custom answer other',
            'user_id'      => $users[2]->id,
            'roll_call_id' => $rollCall->id,
        ]);

        DB::table('roll_call_recipients')
            ->where('roll_call_id', '=', $rollCall->id)
            ->where('user_id', '=', $users[2]->id)
            ->update(['response_status' => 'replied']);

    }
}
