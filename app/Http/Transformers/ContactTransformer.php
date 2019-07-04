<?php
namespace TenFour\Http\Transformers;

use League\Fractal\TransformerAbstract;

class ContactTransformer extends TransformerAbstract
{
    public function transform(array $contact)
    {
        $contact['id'] =  (int) $contact['id'];
        $contact['uri'] = '/contacts/' . $contact['id'];

        if (! isset($contact['user']) && isset($contact['user_id'])) {
            $contact['user']['id'] = $contact['user_id'];
            unset($contact['user_id']);

            $contact['user']['id'] = (int) $contact['user']['id'];
            $contact['user']['uri'] = '/users/' . $contact['user']['id'];
        }

        return $contact;
    }
}
