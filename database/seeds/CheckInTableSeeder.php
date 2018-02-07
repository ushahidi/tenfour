<?php
namespace TenFour\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Notification;

use TenFour\Models\Organization;
use TenFour\Models\User;
use TenFour\Models\Contact;
use TenFour\Models\CheckIn;
use TenFour\Models\Reply;
use TenFour\Notifications\CheckInReceived;
use TenFour\Notifications\ReplyReceived;
use DB;

class CheckInTableSeeder extends Seeder
{
    protected function addUsersToRollCall($users, $check_in) {
        $recipients = [];

        foreach ($users as $user) {
            array_push($recipients, $user->id);
        }

        $check_in->recipients()->sync($recipients, false);
    }

    protected function addReply($check_in, $user, $message, $answer) {
        $reply = Reply::create([
            'message'      => $message,
            'answer'       => $answer,
            'user_id'      => $user->id,
            'check_in_id'   => $check_in->id,
        ]);

        DB::table('check_in_recipients')
            ->where('check_in_id', '=', $check_in->id)
            ->where('user_id', '=', $user->id)
            ->update(['response_status' => 'replied']);

        Notification::send($check_in->recipients, new ReplyReceived($reply));
    }

    protected function addRollCalls($organization, $users, $answers) {
        foreach ($users as $user) {
            $check_in = CheckIn::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id
            ]);

            $check_in->update([
                'message' => 'Test check-in',
                'answers' => $answers,
                'send_via' => ['preferred'],
            ]);

            $this->addUsersToRollCall($users, $check_in);

            Notification::send($check_in->recipients, new CheckInReceived($check_in));

            if (count($answers) > 0) {
                $this->addReply($check_in, $users[1], 'I am not ok', $answers[0]['answer']);
                $this->addReply($check_in, $users[1], 'I am not ok (duplicate reply)', $answers[0]['answer']);
            }

            if (count($answers) > 1) {
                $this->addReply($check_in, $users[2], 'I am ok', $answers[1]['answer']);
            }

            $this->addReply($check_in, $users[3], 'I am not sure', 'other answer');
            $this->addReply($check_in, $users[3], 'I am not sure (deplicate reply)', 'other answer');
        }

        return $check_in;
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
