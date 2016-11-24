<?php
namespace RollCall\Http\Transformers;

use League\Fractal\TransformerAbstract;

class RollCallTransformer extends TransformerAbstract
{

    public function transform(array $roll_call)
    {
        // Format contacts if they are present
        if (isset($roll_call['messages'])) {
            foreach ($roll_call['messages'] as &$contact)
            {
                $contact['id'] = (int) $contact['id'];
                $contact['uri'] = '/contacts/' . $contact['id'];

                // Format contact user
                if (isset($contact['user']['id'])) {
                    $contact['user']['id'] = (int) $contact['user']['id'];
                    $contact['user']['uri'] = '/users/' . $contact['user']['id'];
                }

                unset($contact['user_id']);
                unset($contact['pivot']);
            }
        }

        // Format recipients if they are present
        if (isset($roll_call['recipients'])) {
            foreach ($roll_call['recipients'] as &$user)
            {
                $user['id'] = (int) $user['id'];
                $user['uri'] = '/users/' . $user['id'];

                unset($user['pivot']);
            }
        }

        // Format replies if present
        if (isset($roll_call['replies'])) {
            foreach ($roll_call['replies'] as &$reply)
            {
                $reply['uri'] = '/rollcalls/' . $roll_call['id'] . '/replies/' . $reply['id'];

                // Format contact
                $reply['contact']['id'] = (int) $reply['contact_id'];
                $reply['contact']['uri'] = '/contacts/' . $reply['contact_id'];
                unset($reply['contact_id']);
                unset($reply['roll_call_id']);

                $reply['user']['id'] = (int) $reply['user_id'];
                $reply['user']['uri'] = '/users/' . $reply['user_id'];
                unset($reply['user_id']);

                // Format user
                if (isset($reply['contact']['user'])) {
                    $reply['contact']['user']['id'] = (int) $reply['contact']['user']['id'];
                    $reply['contact']['user']['uri'] = '/users/' . $reply['contact']['user']['id'];
                }
            }
        }

        $roll_call['organization'] = [
            'id'  => (int) $roll_call['organization_id'],
            'uri' => '/organizations/' . $roll_call['organization_id'],
        ];

        unset($roll_call['organization_id']);

        $roll_call['user'] = [
            'id' => (int) $roll_call['user_id'],
            'uri' => '/users/' . $roll_call['user_id'],
        ];

        unset($roll_call['user_id']);

        $roll_call['id'] = (int) $roll_call['id'];
        $roll_call['uri'] = '/rollcalls/' . $roll_call['id'];

        return $roll_call;
    }
}
