<?php
namespace RollCall\Repositories;

use RollCall\Models\RollCall;
use RollCall\Models\Reply;
use RollCall\Contracts\Repositories\RollCallRepository;
use DB;

class EloquentRollCallRepository implements RollCallRepository
{
    public function all($org_id = null, $user_id = null)
    {
        $query = RollCall::query();

        if ($org_id) {
            $query->where('organization_id', $org_id);
        }

        if ($user_id) {
            $query->where('user_id', $user_id);
        }

        $roll_calls = $query->get()->toArray();

        // Add reply and sent counts
        foreach($roll_calls as &$roll_call)
        {
            $roll_call += $this->getCounts($roll_call['id']);
        }

        return $roll_calls;
    }

    public function find($id)
    {
        $roll_call = RollCall::findOrFail($id)
                   ->toArray();

        return $roll_call + $this->getCounts($roll_call['id']);
    }

    public function create(array $input)
    {
        $rollcall = RollCall::create($input);

        $userIds = collect($input['recipients'])->pluck('id')->all();
        $rollcall->recipients()->sync($userIds);

        return $rollcall->fresh()
            ->toArray();
    }

    public function update(array $input, $id)
    {
        $input = array_only($input, ['status', 'sent']);

        $roll_call = RollCall::findorFail($id);
        $roll_call->sent = $input['sent'];
        $roll_call->status = $input['status'];
        $roll_call->save();

        if (isset($input['recipients'])) {
            $userIds = collect($input['recipients'])->pluck('id')->all();
            $rollcall->recipients()->sync($userIds);
        }

        return $roll_call->fresh()->toArray();
    }

    public function getMessages($id)
    {
        return RollCall::findOrFail($id)->messages()->with('user')->get()->toArray();
    }

    public function getRecipients($id, $unresponsive=null)
    {
        $query = RollCall::findOrFail($id)->recipients();

        if ($unresponsive) {
            $query->leftJoin('replies', 'users.id', '=', 'replies.user_id')
                ->where('replies.user_id', '=', null);
        }

        return $query->get()->toArray();


        // return RollCall::with([
        //     'recipients' => function ($query) use ($unresponsive) {
        //         if ($unresponsive) {
        //             $query->leftJoin('replies', 'users.id', '=', 'replies.user_id')
        //                 ->where('replies.user_id', '=', null);
        //         }
        //     }
        // ])
        // ->findOrFail($id)
        // ->toArray();
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

    public function addReply(array $input, $id)
    {
        $roll_call = RollCall::findorFail($id);

        return Reply::create($input)->toArray();
    }

    public function delete($id)
    {
        //
    }

    protected function getReplyCounts($id)
    {
        return Reply::where('roll_call_id', $id)
            ->count();
    }

    protected function getSentCounts($id)
    {
        return DB::table('roll_call_messages')
            ->where('roll_call_id', $id)
            ->count();
    }

    public function getCounts($rollCallId)
    {
        return [
            'reply_count' => $this->getReplyCounts($rollCallId),
            'sent_count' => $this->getSentCounts($rollCallId)
        ];
    }
}
