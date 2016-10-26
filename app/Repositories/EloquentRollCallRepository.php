<?php
namespace RollCall\Repositories;

use RollCall\Models\RollCall;
use RollCall\Models\Reply;
use RollCall\Contracts\Repositories\RollCallRepository;
use DB;

class EloquentRollCallRepository implements RollCallRepository
{
    public function all($org_id = null)
    {
        $roll_calls = null;
        if ($org_id) {
            $roll_calls = RollCall::where('organization_id', $org_id)
                        ->get()
                        ->toArray();
        } else {
            $roll_calls = RollCall::all()
                        ->toArray();
        }

        // Add reply and sent counts
        foreach($roll_calls as &$roll_call)
        {
            $roll_call = $this->addCounts($roll_call);
        }

        return $roll_calls;
    }

    public function find($id)
    {
        $roll_call = RollCall::findOrFail($id)
                   ->toArray();

        return $this->addCounts($roll_call);
    }

    public function create(array $input)
    {
        return RollCall::create($input)
            ->toArray();
    }

    public function update(array $input, $id)
    {
        $input = array_only($input, ['status', 'sent']);
        $roll_call = RollCall::findorFail($id);

        $roll_call->sent = $input['sent'];
        $roll_call->status = $input['status'];
        $roll_call->save();
        return $roll_call->toArray();
    }

    public function getContacts($id, $unresponded=null)
    {
        return RollCall::with([
            'contacts' => function ($query) use ($unresponded) {
                $query->with('user');

                if ($unresponded) {
                    $query->leftJoin('replies', 'contacts.id', '=', 'replies.contact_id')
                        ->where('replies.contact_id', '=', null);
                }

                $query->select('contacts.id', 'contacts.contact', 'contacts.user_id', 'contacts.type');
            }
        ])
            ->findOrFail($id)
            ->toArray();
    }

    public function getReplies($id, $reply_id = null)
    {
        $roll_call = RollCall::with([
            'replies' => function ($query) use ($reply_id) {
                $query->with('contact.user');

                if ($reply_id) {
                    $query->where('replies.id', $reply_id);
                }
            }
        ])
                   ->findOrFail($id)
                   ->toArray();

        return $this->addCounts($roll_call);
    }

    public function addContacts(array $input, $id)
    {
        $roll_call = RollCall::findorFail($id);
        $ids = [];
        $contacts = [];

        // If working with a list of contacts
        if (is_array(head($input))) {
            foreach ($input as $contact)
            {
                array_push($ids, $contact['id']);
            }

            // Add contacts to response
            $contacts = $input;
        }
        else {
            array_push($ids, $input['id']);

            // Add contact to response
            $contacts = [$input];
        }

        DB::transaction(function () use ($roll_call, $ids) {
            $roll_call->contacts()->attach($ids);
        });

        return $roll_call->toArray() +
            [
                'contacts' => $contacts
            ];
    }

    public function addReply(array $input, $id)
    {
        $roll_call = RollCall::findorFail($id);

        $reply = Reply::create($input);

        return $roll_call->toArray() +
            [
                'replies' => [
                    [
                        'id'         => $reply->id,
                        'message'    => $reply->message,
                        'contact_id' => $input['contact_id']
                    ]
                ]
            ];
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
        return DB::table('contact_roll_call')
            ->where('roll_call_id', $id)
            ->count();
    }

    protected function addCounts($roll_call)
    {
        $roll_call['reply_count'] = $this->getReplyCounts($roll_call['id']);
        $roll_call['sent_count'] = $this->getSentCounts($roll_call['id']);

        return $roll_call;
    }
}
