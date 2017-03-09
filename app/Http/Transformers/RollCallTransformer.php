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

        $roll_call['organization'] = [
            'id'  => (int) $roll_call['organization_id'],
            'uri' => '/organizations/' . $roll_call['organization_id'],
        ];

        unset($roll_call['organization_id']);

        $roll_call['id'] = (int) $roll_call['id'];
        $roll_call['uri'] = '/rollcalls/' . $roll_call['id'];

        return $roll_call;
    }

    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        'user',
        'replies',
        'recipients'
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'user',
        'replies',
        'recipients'
    ];

    /**
     * Include Author
     *
     * @return League\Fractal\ItemResource
     */
    public function includeUser(array $roll_call)
    {
        $user = $roll_call['user'];

        return $this->item($user, new UserTransformer);
    }

    /**
     * Include Author
     *
     * @return League\Fractal\ItemResource
     */
    public function includeRecipients(array $roll_call)
    {
        $recipients = $roll_call['recipients'];

        return $this->collection($recipients, new UserTransformer);
    }

    /**
     * Include Author
     *
     * @return League\Fractal\ItemResource
     */
    public function includeReplies(array $roll_call)
    {
        $replies = $roll_call['replies'];

        return $this->collection($replies, new ReplyTransformer);
    }

}
