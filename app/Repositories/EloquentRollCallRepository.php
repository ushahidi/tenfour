<?php
namespace RollCall\Repositories;

use RollCall\Models\RollCall;
use RollCall\Models\Reply;
use RollCall\Contracts\Repositories\RollCallRepository;
use DB;

class EloquentRollCallRepository implements RollCallRepository
{
    public function all($org_id = null, $user_id = null, $recipient_id = null)
    {
        $query = RollCall::query();

        if ($org_id) {
            $query->where('organization_id', $org_id);
        }

        if ($user_id) {
            $query->where('user_id', $user_id);
        }

        if ($recipient_id) {
            $query->whereHas('recipients', function ($query) use ($recipient_id) {
                $query->where('user_id', $recipient_id);
            });
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
        $roll_call = RollCall::create($input);

        $userIds = collect($input['recipients'])->pluck('id')->all();
        $roll_call->recipients()->sync($userIds);

        return $roll_call->fresh()
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
            $roll_call->recipients()->sync($userIds);
        }

        return $roll_call->fresh()->toArray();
    }

    public function getMessages($id, $user_id = null, $contact_id = null)
    {
        $query = RollCall::findOrFail($id)->messages()->with('user');

        if ($user_id) {
            $query->where('user_id', $user_id);
        }

        return $query->get()->toArray();
    }

    public function getLastSentMessageId($contact_id = null)
    {
        $query = DB::table('roll_call_messages')
               ->select('roll_call_id');

        if ($contact_id) {
            $query->where('contact_id', $contact_id);
        }

        return $query->orderBy('roll_call_id', 'desc')->take(1)->value('roll_call_id');
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

    public function addMessage($id, $contact_id)
    {
        $roll_call = RollCall::findorFail($id);

        $roll_call->messages()->attach($contact_id);

        return RollCall::findorFail($id)
            ->messages()
            ->where('contact_id', $contact_id)
            ->get()
            ->toArray();
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
