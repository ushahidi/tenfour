<?php
namespace RollCall\Repositories;

use RollCall\Models\Reply;
use RollCall\Contracts\Repositories\ReplyRepository;
use DB;

class EloquentReplyRepository implements ReplyRepository
{
    public function create(array $input)
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

    public function find($id)
    {
        $reply = Reply::findOrFail($id)
                 ->toArray();

        return $reply;
    }

    public function delete($id)
    {

    }

    public function all() {

    }
}
