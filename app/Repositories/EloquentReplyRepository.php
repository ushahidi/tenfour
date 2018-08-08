<?php
namespace TenFour\Repositories;

use TenFour\Models\Reply;
use TenFour\Models\CheckIn;
use TenFour\Models\User;
use TenFour\Messaging\AnswerParser;
use TenFour\Contracts\Repositories\CheckInRepository;
use TenFour\Contracts\Repositories\ReplyRepository;
use TenFour\Contracts\Repositories\ContactRepository;
use TenFour\Services\AnalyticsService;
use TenFour\Notifications\ReplyReceived;

use DB;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class EloquentReplyRepository implements ReplyRepository
{
    public function __construct(CheckInRepository $check_ins, ContactRepository $contacts)
    {
        $this->check_ins = $check_ins;
        $this->contacts = $contacts;
    }

    // TODO DEPRECATED Consider merging this with `addReply`
    public function create(array $input)
    {
        return Reply::create($input)->toArray();
    }

    public function addReply(array $input, $id)
    {
        $check_in = CheckIn::findOrFail($id);

        $input['response_time'] = Carbon::now()->diffInSeconds($check_in['created_at']);

        $reply = Reply::create($input)->toArray();

        Notification::send($check_in->recipients,
            new ReplyReceived(new Reply($reply)));

        (new AnalyticsService())->track('CheckIn Responded', [
            'org_id'            => $check_in['organization_id'],
            'check_in_id'        => $check_in['id'],
            'user_id'           => $check_in['user_id'],
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
        $query = CheckIn::findOrFail($id)
            ->replies()
            ->with('user')
            // Just get the most recent replies for each user
            ->where('created_at', DB::raw("(SELECT max(`r2`.`created_at`) FROM `replies` AS r2 WHERE `r2`.`user_id` = `replies`.`user_id` AND `r2`.`check_in_id` = `replies`.`check_in_id`)"));

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

    public function save($from, $message, $message_id = 0, $check_in_id = null, $provider = null, $outgoing_number = null)
    {
        $contact = $this->contacts->getByMostRecentlyUsedContact($from);

        if ($contact) {

            if ($check_in_id) {
                // Just check the check-in was actually sent to this user
                $check_in_id = $this->check_ins->getSentCheckInId($contact['id'], $check_in_id);
            } else {
                // Get last check-in id that was sent to the the the contact
                $check_in_id = $this->check_ins->getLastSentMessageId($contact['id'], $outgoing_number);
            }

            // Add reply if check-in exists
            if ($check_in_id) {

                $check_in = $this->check_ins->find($check_in_id);

                $answer = AnswerParser::parse($message, $check_in['answers']);

                $input = [
                    'message'      => $message,
                    'user_id'      => $contact['user']['id'],
                    'check_in_id'  => $check_in_id,
                    'contact_id'   => $contact['id'],
                    'answer'       => $answer,
                    'message_id'   => $message_id,
                ];

                $this->addReply($input, $check_in_id);

                // Update response status
                $this->check_ins->updateRecipientStatus($check_in_id, $contact['user']['id'], 'replied');

                // Update user checklist self test status
                if ($check_in['self_test_check_in']) {
                    $this->updateUserSelfTest($contact['user']['id']);
                }

                if (!$outgoing_number) {
                    $outgoing_number = $this->check_ins->getOutgoingNumberForCheckInToContact($check_in_id, $contact['id']);
                }

                return [
                  "check_in_id"     => $check_in_id,
                  "contact_id"      => $contact['id'],
                  "from"            => $outgoing_number,
                  "organization_id" => $check_in['organization_id'],
                ];
            } else {
                \Log::warning('Could not find the check-in for incoming message from ' . $from);
                app('sentry')->captureMessage('Could not find the check-in for incoming message from ' . $from);
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
