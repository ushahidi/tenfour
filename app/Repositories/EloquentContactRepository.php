<?php
namespace RollCall\Repositories;

use RollCall\Models\Contact;
use RollCall\Contracts\Repositories\ContactRepository;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

class EloquentContactRepository implements ContactRepository
{
    public function __construct()
    {
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
    }

    public function all()
    {
        return Contact::with([
            'user' => function ($query) {
                $query->select('users.id', 'users.name');
            }
        ])
            ->get()
            ->toArray();
    }

    public function update(array $input, $id)
    {
        $this->normalizeContact($input);

		    $contact = Contact::findorFail($id);
        $contact->update($input);

        return $contact->toArray();
    }

    public function create(array $input)
    {
        $this->normalizeContact($input);

        $contact = Contact::create($input);

        return $contact->toArray();
    }

    protected function normalizeContact(&$input)
    {
        $input['contact'] = trim($input['contact']);;

        if ($input['type'] === 'phone') {
            $input['contact'] = $this->phoneNumberUtil->format(
              $this->phoneNumberUtil->parse($input['contact'], null),
              PhoneNumberFormat::E164);

            // TODO this is where we would save the original
            // phone number and the region, etc
        }
    }

    public function find($id)
    {
        return Contact::with([
            'user' => function ($query) {
                $query->select('users.id', 'users.name');
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

    public function getByUserId($user_id, Array $methods = null)
    {
        $query = Contact::with([
            'user' => function ($query) {
                $query->select('users.id', 'users.name');
            }
        ])
            ->where('user_id', $user_id);

        // Filter contacts by method if required
        if (!empty($methods)) {
            $methods = array_map(function ($method) {
                return $method === 'sms' ? 'phone' : $method;
            }, $methods);

            $query->where(function ($query) use ($methods) {
                $query->whereIn('type', $methods);

                if (in_array('preferred', $methods)) {
                    $query->orWhere('preferred', 1);
                }
            });
        }

        return $query->get()
            ->toArray();
    }

    public function getByContact($contact)
    {
        $contact = Contact::with([
            'user' => function ($query) {
                $query->select('users.id', 'users.name');
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

    public function unsubscribe($token)
    {
      $contact = Contact::where('unsubscribe_token', $token)->firstOrFail();

      $contact['blocked'] = true;
      $contact->save();

      return $contact->toArray();
    }

    public function setBounceCount($count, $id)
    {
        $contact = Contact::findorFail($id);
        $contact->bounce_count = $count;
        $contact->save();

        return $contact->fresh()->toArray();
    }
}
