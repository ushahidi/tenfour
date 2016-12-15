<?php
namespace RollCall\Repositories;

use RollCall\Models\Contact;
use RollCall\Contracts\Repositories\ContactRepository;

class EloquentContactRepository implements ContactRepository
{
    public function all()
    {
        return Contact::with([
            'user' => function ($query) {
                $query->select('users.id', 'users.name', 'users.email');
            }
        ])
            ->get()
            ->toArray();
    }

    public function update(array $input, $id)
    {
		$contact = Contact::findorFail($id);
        $contact->update($input);

        return $contact->toArray();
    }

    public function create(array $input)
    {
        $contact = Contact::create($input);

        return $contact->toArray();
    }

    public function find($id)
    {
        return Contact::with([
            'user' => function ($query) {
                $query->select('users.id', 'users.name', 'users.email');
            }
        ])
            ->findOrFail($id)
            ->toArray();
    }

    public function delete($id)
    {
		$contact = Contact::findOrFail($id);
		$contact->delete();

        return $contact->toArray();
    }

    public function getByUserId($user_id)
    {
        return Contact::with([
            'user' => function ($query) {
                $query->select('users.id', 'users.name', 'users.email');
            }
        ])
            ->where('user_id', $user_id)
            ->get()
            ->toArray();
    }

    public function getByContact($contact)
    {
        $contact = Contact::with([
            'user' => function ($query) {
                $query->select('users.id', 'users.name', 'users.email');
            }
        ])
            ->where('contact', $contact)
            ->get();

        if (!$contact->isEmpty()) {
            $contact = $contact->first()->toArray();
        } else {
            $contact = $contact->toArray();
        }

        return $contact;
    }
}
