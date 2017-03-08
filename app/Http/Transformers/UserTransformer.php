<?php
namespace RollCall\Http\Transformers;

use League\Fractal\TransformerAbstract;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class UserTransformer extends TransformerAbstract
{
    public static function generateInitials($name) {
        $initials = array_map(function ($word) {
            return substr($word, 0, 1);
        }, explode(' ', $name));
        $initials = strtoupper(implode('', $initials));
        return substr($initials, 0, 3);
    }
    public function transform(array $user)
    {
        $user['uri'] = '/users/' . $user['id'];

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

                    // Set Gravatar ID from the first email found?
                    if (! isset($contact['user']['gravatar'])) {
                        $contact['user']['gravatar'] = ! empty($contact['contact']) ? md5(strtolower(trim($contact['contact']))) : '00000000000000000000000000000000';

                        // Set Gravatar ID from contact
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

        if (isset($user['notifications'])) {
            foreach($user['notifications'] as &$notification) {

                $notification['type'] = str_replace('RollCall\\Notifications\\', '', $notification['type']);
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


        // Got response status if available
        if (isset($user['pivot']['response_status'])) {
            $user['response_status'] = $user['pivot']['response_status'];
        }

        unset($user['pivot']);

        // Generate user initials
        $user['initials'] = $this->generateInitials($user['name']);

        return $user;
    }
}
