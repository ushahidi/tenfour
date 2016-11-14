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

    public function getContacts($id, $unresponsive=null)
    {
        return RollCall::with([
            'contacts' => function ($query) use ($unresponsive) {
                $query->with('user');

                if ($unresponsive) {
                    $query->leftJoin('replies', 'contacts.id', '=', 'replies.contact_id')
                        ->where('replies.contact_id', '=', null);
                }

                $query->select('contacts.id', 'contacts.contact', 'contacts.user_id', 'contacts.type');
            }
        ])
            ->findOrFail($id)
            ->toArray();
    }

    public function getReplies($id, $contacts = null)
    {
        if ($contacts) {
            $contacts = explode(',', $contacts);
        }

        $roll_call = RollCall::with([
            'replies' => function ($query) use ($contacts) {
                $query->with('contact.user');

                if ($contacts) {
                    $query->whereIn('replies.contact_id', $contacts);
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

        foreach ($input as &$contact)
        {
            $contact = array_only($contact, ['id']);
            array_push($ids, $contact['id']);
        }

        DB::transaction(function () use ($roll_call, $ids) {
            $roll_call->contacts()->attach($ids);
        });

        return $input;
    }

    public function addContact(array $input, $id)
    {
        $input = array_only($input, ['id']);

        RollCall::findorFail($id)
            ->contacts()
            ->attach($input['id']);

        return $input;
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
