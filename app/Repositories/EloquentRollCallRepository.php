<?php
namespace RollCall\Repositories;

use RollCall\Models\RollCall;
use RollCall\Contracts\Repositories\RollCallRepository;
use DB;

class EloquentRollCallRepository implements RollCallRepository
{
    public function all()
    {
        $roll_calls = RollCall::all();

        return $roll_calls->toArray();
    }

    public function filterByOrganizationId($org_id)
    {
        $roll_calls = RollCall::where('organization_id', $org_id)
                   ->get();

        return $roll_calls->toArray();
    }

    public function find($id)
    {
        $roll_call = RollCall::find($id);

        return $roll_call->toArray();
    }

    public function create(array $input)
    {
        $roll_call = RollCall::create($input);

        return $roll_call->toArray();
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

    public function getContacts($id)
    {
        return RollCall::with([
            'contacts' => function($query) {
                $query->select('contacts.id', 'contacts.contact', 'contacts.user_id');
            }
        ])
            ->findOrFail($id)
            ->toArray();
    }

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


    public function delete($id)
    {
        //
    }
}
