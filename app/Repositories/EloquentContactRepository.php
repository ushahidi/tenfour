<?php
namespace RollCall\Repositories;

use RollCall\Models\Contact;
use RollCall\Contracts\Repositories\ContactRepository;

class EloquentContactRepository implements ContactRepository
{
    public function all()
    {
        return Contact::all();
    }

    public function update(array $input, $id)
    {
		$contact = Contact::findorFail($id);
        $contact->update($input);
		return $contact;
    }

    public function create(array $input)
    {
        return Contact::create($input);
    }

    public function find($id)
    {
        return Contact::find($id);
    }

    public function delete($id)
    {
		$contact = Contact::findorFail($id);
		$contact->delete();
        return $contact;
    }
    
}
