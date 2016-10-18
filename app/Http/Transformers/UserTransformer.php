<?php
namespace RollCall\Http\Transformers;

use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(array $user)
    {
        // Format contacts if present
        if (isset($user['contacts'])) {
            foreach($user['contacts'] as &$contact)
            {
                $contact['id'] = (int) $contact['id'];
                $contact['uri'] = '/contact/' . $contact['id'];
                unset($contact['user_id']);
            }
        }

        return $user;
    }
}
