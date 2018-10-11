<?php
namespace TenFour\Repositories;

use TenFour\Models\Contact;
use TenFour\Notifications\Unsubscribe;
use TenFour\Contracts\Repositories\ContactRepository;
use TenFour\Services\AnalyticsService;

use Illuminate\Support\Facades\Notification;
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
        $contact = Contact::findorFail($id);
        $contact->update($input);

        return $contact->toArray();
    }

    public function create(array $input)
    {
        $contact = Contact::create($input);

        (new AnalyticsService())->track('Contact Added', [
            'org_id'          => $contact->organization_id,
            'contact_id'      => $contact->id,
            'total_contacts'  => $this->getOrganizationContactCount($contact->organization_id),
        ]);

        return $contact->toArray();
    }

    private function getOrganizationContactCount($org_id)
    {
        return Contact::where('organization_id', '=', $org_id)->count();
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

        (new AnalyticsService())->track('Contact Removed', [
            'org_id'          => $contact->organization_id,
            'contact_id'      => $id,
            'total_contacts'  => $this->getOrganizationContactCount($contact->organization_id),
        ]);

    		$contact->delete();

        return $contact->toArray();
    }

    public function getByUserId($user_id, Array $methods = null)
    {
        $query = Contact::with([
            'user' => function ($query) {
                $query->select('users.id', 'users.name');
            }
        ]);

        if (is_array($user_id)) {
            $query = $query->whereIn('user_id', $user_id);
        } else {
            $query = $query->where('user_id', $user_id);
        }

        // Filter contacts by method if required
        if (!empty($methods)) {
            $methods = array_map(function ($method) {
                return $method === 'sms' || $method === 'voice' ? 'phone' : $method;
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

    public function getByContact($contact, $org_id = null)
    {
        $contact = Contact::with([
            'user' => function ($query) {
                $query->select('users.id', 'users.name');
            }
        ])
            ->where('contact', 'like', $contact);

        if ($org_id) {
            $contact->where('organization_id', '=', $org_id);
        }

        $contact = $contact->get();

        if (!$contact->isEmpty()) {
            $contact = $contact->first()->toArray();
        } else {
            $contact = $contact->toArray();
        }

        return $contact;
    }

    public function getByMostRecentlyUsedContact($contact)
    {
        return Contact::with([
            'user' => function ($query) {
                $query->select('users.id', 'users.name');
            }])
            ->leftJoin('check_in_messages', 'check_in_messages.contact_id', '=', 'contacts.id')
            ->where('contact', 'like', $contact)
            ->orderBy('check_in_messages.created_at', 'desc')
            ->first();
    }

    public function unsubscribe($token)
    {
      $contact = Contact::where('unsubscribe_token', $token)->firstOrFail();

      $contact['blocked'] = true;
      $contact->save();

      $people = resolve('TenFour\Contracts\Repositories\PersonRepository');

      Notification::send($people->getAdmins($contact->user->organization->id),
          new Unsubscribe($contact->user, $contact));

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
