<?php
namespace RollCall\Repositories;

use RollCall\Models\RollCall;
use RollCall\Contracts\Repositories\RollCallRepository;
use DB;

class EloquentRollCallRepository implements RollCallRepository
{
    public function all()
    {
        $rollCalls = RollCall::all();

        return $rollCalls->toArray();
    }

    public function filterByOrganizationId($org_id)
    {
        $rollCalls = RollCall::where('organization_id', $org_id)
                   ->get();

        return $rollCalls->toArray();
    }

    public function find($id)
    {
        $rollCall = RollCall::find($id);

        return $rollCall->toArray();
    }

    public function create(array $input)
    {
        $rollCall = RollCall::create($input);

        return $rollCall->toArray();
    }

    public function update(array $input, $id)
    {
        $input = array_only($input, ['status', 'sent']);

        $rollCall = RollCall::findorFail($id);
        $rollCall->status = $input['status'];
        $rollCall->sent = $input['sent'];
        $rollCall->save();
        return $rollCall->toArray();
    }

    public function listContacts($id)
    {
        $rollCall = RollCall::findorFail($id);

        $contacts = RollCall::with('contacts')
                  ->findOrFail($id)
                  ->contacts()
                  ->select('contacts.id', 'contacts.contact')
                  ->get()
                  ->toArray();

        foreach($contacts as &$contact)
        {
            unset($contact['pivot']);
        }

        return $rollCall->toArray() + [
            'contacts' => $contacts
        ];
    }

    public function addContacts(array $input, $id)
    {
        $rollCall = RollCall::findorFail($id);
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

        DB::transaction(function () use ($rollCall, $ids) {
            $rollCall->contacts()->attach($ids);
        });

        return $rollCall->toArray() +
        [
            'contacts' => $contacts
        ];
    }


    public function delete($id)
    {
        //
    }
}
