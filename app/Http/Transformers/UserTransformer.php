<?php
namespace RollCall\Http\Transformers;

use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(array $user)
    {
        // User config task
        if (! empty($user['config_profile_reviewed']) && ! empty($user['config_self_test_sent'])) {
            $user['configComplete'] = $user['config_profile_reviewed']
                                    && $user['config_self_test_sent'];
        }

        // Format contacts if present
        if (isset($user['contacts'])) {
            foreach($user['contacts'] as &$contact)
            {
                $contact['id'] = (int) $contact['id'];
                $contact['uri'] = '/contact/' . $contact['id'];
                if ($contact['type'] === 'email') {

                    // Set Gratar ID from the first email found?
                    if (! empty($user['gravatar'])) {
                        $contact['user']['gravatar'] = !empty($contact['contact']) ? md5(strtolower(trim($contact['contact']))) : '00000000000000000000000000000000';

                        // Do we need to set this twice?
                        $user['gravatar'] = $contact['user']['gravatar'];
                    }
                }

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

        return $user;
    }
}
