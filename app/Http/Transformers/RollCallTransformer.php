<?php
namespace RollCall\Http\Transformers;

use League\Fractal\TransformerAbstract;

class RollCallTransformer extends TransformerAbstract
{
    public function transform(array $roll_call)
    {
        // Format contacts if they are present
        if (isset($roll_call['contacts'])) {
            foreach ($roll_call['contacts'] as &$contact)
            {
                $contact['id'] = (int) $contact['id'];
                $contact['uri'] = '/contacts/' . $contact['id'];

                // Format contact user
                if (isset($contact['user_id'])) {
                    $contact['user']['id'] = (int) $contact['user_id'];
                    $contact['user']['uri'] = '/users/' . $contact['user_id'];
                    unset($contact['user_id']);
                }

                unset($contact['pivot']);
            }
        }

        // Format replies if present
        if (isset($roll_call['replies'])) {
            foreach ($roll_call['replies'] as &$reply)
            {
                $reply['url'] = '/rollcalls/' . $roll_call['id'] . '/replies/' . $reply['id'];

                // Format contact
                $reply['contact']['id'] = (int) $reply['contact_id'];
                $reply['contact']['uri'] = '/contacts/' . $reply['contact_id'];
                unset($reply['contact_id']);
                unset($reply['roll_call_id']);
            }
        }


        $roll_call['organization'] = [
            'id'  => (int) $roll_call['organization_id'],
            'uri' => '/organizations/' . $roll_call['organization_id'],
        ];

        unset($roll_call['organization_id']);

        $roll_call['id'] = (int) $roll_call['id'];
        $roll_call['uri'] = '/rollcalls/' . $roll_call['id'];

        return $roll_call;
    }
}
