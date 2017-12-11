<?php
namespace RollCall\Repositories;

use RollCall\Models\Reply;
use RollCall\Models\RollCall;
use RollCall\Models\User;
use RollCall\Messaging\AnswerParser;
use RollCall\Contracts\Repositories\RollCallRepository;
use RollCall\Contracts\Repositories\ReplyRepository;
use RollCall\Contracts\Repositories\ContactRepository;
use RollCall\Services\AnalyticsService;
use RollCall\Notifications\ReplyReceived;

use DB;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class EloquentReplyRepository implements ReplyRepository
{
    public function __construct(RollCallRepository $roll_calls, ContactRepository $contacts)
    {
        $this->roll_calls = $roll_calls;
        $this->contacts = $contacts;
    }

    // TODO DEPRECATED Consider merging this with `addReply`
    public function create(array $input)
    {
        return Reply::create($input)->toArray();
    }

    public function addReply(array $input, $id)
    {
        $rollcall = RollCall::findOrFail($id);

        $input['response_time'] = Carbon::now()->diffInSeconds($rollcall['created_at']);

        $reply = Reply::create($input)->toArray();

        Notification::send($rollcall->recipients,
            new ReplyReceived(new Reply($reply)));

        (new AnalyticsService())->track('RollCall Responded', [
            'org_id'            => $rollcall['organization_id'],
            'roll_call_id'      => $rollcall['id'],
            'user_id'           => $rollcall['user_id'],
            'response_time'     => $input['response_time'],
        ]);

        return $reply;
    }

    public function update(array $input, $id)
    {
        $reply = Reply::findorFail($id);

        if (isset($input['answer'])) {
            $reply->answer = $input['answer'];
        }

        $reply->message = $input['message'];
        $reply->location_text = $input['location_text'];
        $reply->save();
        return $reply->toArray();
    }

    public function getReplies($id, $users = null, $contacts = null)
    {
        $query = RollCall::findOrFail($id)
            ->replies()
            ->with('user')
            // Just get the most recent replies for each user
            ->where('created_at', DB::raw("(SELECT max(`r2`.`created_at`) FROM `replies` AS r2 WHERE `r2`.`user_id` = `replies`.`user_id` AND `r2`.`roll_call_id` = `replies`.`roll_call_id`)"));

        if ($users) {
            $users = explode(',', $users);
            $query->whereIn('replies.user_id', $users);
        }

        if ($contacts) {
            $contacts = explode(',', $contacts);
            $query->whereIn('replies.contact_id', $contacts);
        }

        return $query->get()->toArray();
    }

    public function find($id)
    {
        $reply = Reply::findOrFail($id)
                 ->toArray();

        return $reply;
    }

    public function getLastReplyId()
    {
        // Assumes the provider id is incremental
        return Reply::max('message_id');
    }

    public function delete($id)
    {
        //
    }

    public function all()
    {
        //
    }

    public function save($from, $message, $message_id = 0, $roll_call_id = null, $provider = null, $outgoing_number = null)
    {
        $contact = $this->contacts->getByMostRecentlyUsedContact($from);

        if ($contact) {

            if ($roll_call_id) {
                // Just check the rollcall was actually sent to this user
                $roll_call_id = $this->roll_calls->getSentRollCallId($contact['id'], $roll_call_id);
            } else {
                // Get last roll call id that was sent to the the the contact
                $roll_call_id = $this->roll_calls->getLastSentMessageId($contact['id'], $outgoing_number);
            }

            // Add reply if roll call exists
            if ($roll_call_id) {

                $roll_call = $this->roll_calls->find($roll_call_id);

                $answer = AnswerParser::parse($message, $roll_call['answers']);

                $input = [
                    'message'      => $message,
                    'user_id'      => $contact['user']['id'],
                    'roll_call_id' => $roll_call_id,
                    'contact_id'   => $contact['id'],
                    'answer'       => $answer,
                    'message_id'   => $message_id,
                ];

                $this->addReply($input, $roll_call_id);

                // Update response status
                $this->roll_calls->updateRecipientStatus($roll_call_id, $contact['user']['id'], 'replied');

                // Update user checklist self test status
                if ($roll_call['self_test_roll_call']) {
                    $this->updateUserSelfTest($contact['user']['id']);
                }

                if (!$outgoing_number) {
                    $outgoing_number = $this->roll_calls->getOutgoingNumberForRollCallToContact($roll_call_id, $contact['id']);
                }

                return [
                  "roll_call_id"  => $roll_call_id,
                  "contact_id"    => $contact['id'],
                  "from"          => $outgoing_number
                ];
            } else {
                \Log::warning('Could not find the RollCall for incoming message from ' . $from);
                app('sentry')->captureMessage('Could not find the RollCall for incoming message from ' . $from);
            }
        } else {
            \Log::warning('Could not find the contact details for incoming message from ' . $from);
            app('sentry')->captureMessage('Could not find the contact details for incoming message from ' . $from);
        }

        return false;
    }

    protected function updateUserSelfTest($user_id) {
        $user = User::findOrFail($user_id);
        $user['config_self_test_sent'] = 1;
        $user->save();
    }
}
