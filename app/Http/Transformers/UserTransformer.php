<?php
namespace RollCall\Http\Transformers;

use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(array $user)
    {
        // User config task
        $user['configComplete'] = $user['config_profile_reviewed']
                                    && $user['config_self_test_sent'];

        // Format contacts if present
        if (isset($user['contacts'])) {
            foreach($user['contacts'] as &$contact)
            {
                $contact['id'] = (int) $contact['id'];
                $contact['uri'] = '/contact/' . $contact['id'];
                $contact['user']['gravatar'] = !empty($contact['user']['email']) ? md5(strtolower(trim($contact['user']['email']))) : '00000000000000000000000000000000';
                unset($contact['user_id']);

                // Format replies
                if (isset($contact['replies'])) {
                    $reply_transformer = new ReplyTransformer;

                    foreach($contact['replies'] as &$reply)
                    {
                        $reply = $reply_transformer->transform($reply);

                        // Remove contact info form reply
                        unset($reply['contact']);
                    }
                }
            }
        }

        // Format roll calls
        if (isset($user['rollcalls'])) {
            $roll_call_transformer = new RollCallTransformer;

            foreach($user['rollcalls'] as &$roll_call)
            {
                $roll_call = $roll_call_transformer->transform($roll_call);

                // Remove user information
                unset($roll_call['user']);
            }
        }

        // Set Gravatar ID
        $user['gravatar'] = !empty($user['email']) ? md5(strtolower(trim($user['email']))) : '00000000000000000000000000000000';
        // Generate user initials
        $user['initials'] =
            array_map(function ($word) {
                return substr($word, 0, 1);
            }, explode(' ', $user['name']));
        $user['initials'] = strtoupper(implode('', $user['initials']));
        return $user;
    }
}
