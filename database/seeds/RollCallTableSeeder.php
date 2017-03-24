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

    protected function addReply($rollCall, $user, $message, $answer) {
        $reply = Reply::create([
            'message'      => $message,
            'answer'       => $answer,
            'user_id'      => $user->id,
            'roll_call_id' => $rollCall->id,
        ]);

        DB::table('roll_call_recipients')
            ->where('roll_call_id', '=', $rollCall->id)
            ->where('user_id', '=', $user->id)
            ->update(['response_status' => 'replied']);

        Notification::send($rollCall->recipients, new ReplyReceived($reply));
    }

    protected function addRollCalls($organization, $users, $answers) {
        foreach ($users as $user) {
            $rollCall = RollCall::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id
            ]);

            $rollCall->update([
                'message' => 'Test rollcall',
                'answers' => $answers
            ]);

            $this->addUsersToRollCall($users, $rollCall);

            Notification::send($rollCall->recipients, new RollCallReceived($rollCall));

            if (count($answers) > 0) {
                $this->addReply($rollCall, $users[1], 'I am not ok', $answers[0]['answer']);
                $this->addReply($rollCall, $users[1], 'I am not ok (duplicate reply)', $answers[0]['answer']);
            }

            if (count($answers) > 1) {
                $this->addReply($rollCall, $users[2], 'I am ok', $answers[1]['answer']);
            }

            $this->addReply($rollCall, $users[3], 'I am not sure', 'other answer');
            $this->addReply($rollCall, $users[3], 'I am not sure (deplicate reply)', 'other answer');
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

        $users = User::select('id')->where('organization_id', $organization->id)->limit(10)->get();

        // default answers

        $this->addRollCalls($organization, $users, [
            ['answer'=>'No','color'=>'#BC6969','icon'=>'icon-exclaim','type'=>'negative'],
            ['answer'=>'Yes','color'=>'#E8C440','icon'=>'icon-check','type'=>'positive']
        ]);

        // custom answers

        $this->addRollCalls($organization, $users, [
          ['answer'=>'Custom answer blue','color'=>'#2274B4','icon'=>'icon-exclaim','type'=>'custom'],
          ['answer'=>'Custom answer teal','color'=>'#4CBFCE','icon'=>'icon-check','type'=>'custom']
        ]);

        // no answers

        $this->addRollCalls($organization, $users, []);

    }
}
