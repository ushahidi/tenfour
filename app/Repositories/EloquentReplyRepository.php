<?php
namespace RollCall\Repositories;

use RollCall\Models\Reply;
use RollCall\Models\RollCall;
use RollCall\Contracts\Repositories\ReplyRepository;
use RollCall\Contracts\Repositories\RollCallRepository;
use DB;

class EloquentReplyRepository implements ReplyRepository
{
    public function create(array $input)
    {
        return Reply::create($input)
            ->toArray();
    }

    public function addReply(array $input, $id)
    {

        return Reply::create($input)
            ->toArray();
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
        $query = RollCall::findOrFail($id)->replies()->with('user');

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
