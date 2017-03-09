<?php
namespace RollCall\Repositories;

use RollCall\Models\Reply;
use RollCall\Models\RollCall;
use RollCall\Contracts\Repositories\ReplyRepository;
use RollCall\Contracts\Repositories\RollCallRepository;
use DB;

use Illuminate\Support\Facades\Notification;
use RollCall\Notifications\ReplyReceived;

class EloquentReplyRepository implements ReplyRepository
{
    public function create(array $input)
    {
        return Reply::create($input)
            ->toArray();
    }

    public function addReply(array $input, $id)
    {
        $reply = Reply::create($input)->toArray();
        $rollcall = RollCall::findOrFail($id);

        Notification::send($rollcall->recipients,
            new ReplyReceived(new Reply($reply)));

        return $reply;
    }

    public function update(array $input, $id)
    {
        $reply = Reply::findorFail($id);
        $reply->answer = $input['answer'];
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
}
