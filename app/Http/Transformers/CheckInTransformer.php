<?php
namespace TenFour\Http\Transformers;

use League\Fractal\TransformerAbstract;

class CheckInTransformer extends TransformerAbstract
{

    public function transform(array $check_in)
    {
        // Format contacts if they are present
        if (isset($check_in['messages'])) {
            foreach ($check_in['messages'] as &$contact)
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

        $check_in['id'] =  (int) $check_in['id'];
        $check_in['uri'] = '/organizations/' . $check_in['organization_id'] . '/checkins/' . $check_in['id'];

        unset($check_in['organization_id']);

        return $check_in;

    }

    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        'user',
        'replies',
        'recipients',
        'groups',
        'users',
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'user',
        'replies',
        'recipients',
        'groups',
        'users'
    ];

    /**
     * Include Author
     *
     * @return League\Fractal\ItemResource
     */
    public function includeUser(array $check_in)
    {
        $user = $check_in['user'];

        return $this->item($user, new UserTransformer);
    }

    /**
     * Include recipients
     *
     * @return League\Fractal\ItemResource
     */
    public function includeRecipients(array $check_in)
    {
        $recipients = $check_in['recipients'];

        return $this->collection($recipients, new UserTransformer);
    }

    /**
     * Include groups
     *
     * @return League\Fractal\ItemResource
     */
    public function includeGroups(array $check_in)
    {
        $groups = $check_in['groups'];

        return $this->collection($groups, new GroupTransformer);
    }

    /**
     * Include users
     *
     * @return League\Fractal\ItemResource
     */
    public function includeUsers(array $check_in)
    {
        $users = $check_in['users'];

        return $this->collection($users, new UserTransformer);
    }

    /**
     * Include replies
     *
     * @return League\Fractal\ItemResource
     */
    public function includeReplies(array $check_in)
    {
        $replies = $check_in['replies'];

        return $this->collection($replies, new ReplyTransformer);
    }

}
