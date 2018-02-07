<?php
namespace TenFour\Http\Transformers;

use League\Fractal\TransformerAbstract;
use TenFour\Http\Transformers\UserTransformer;

class ReplyTransformer extends TransformerAbstract
{
    public function transform(array $reply)
    {
        $reply['uri'] = '/checkins/' . $reply['check_in_id'] . '/reply/' . $reply['id'];

        $reply['check_in']['id'] = (int) $reply['check_in_id'];
        $reply['check_in']['uri'] = '/checkins/' . $reply['check_in_id'];
        unset($reply['check_in_id']);

        if (!empty($reply['contact_id'])) {
            $reply['contact']['id'] = (int) $reply['contact_id'];
            $reply['contact']['uri'] = '/contacts/' . $reply['contact_id'];
            unset($reply['contact_id']);
        }
        // $reply['user']['id'] = (int) $reply['user_id'];
        // $reply['user']['uri'] = '/users/' . $reply['user_id'];
        // $reply['user']['gravatar'] = !empty($reply['user']['email']) ? md5(strtolower(trim($reply['user']['email']))) : '00000000000000000000000000000000';
        // // Set Gravatar ID

        // if(!empty($reply['user']['name']))
        // {
        //         $reply['user']['initials'] = UserTransformer::generateInitials($reply['user']['name']);
        // }
        // unset($reply['user_id']);

        return $reply;
    }


    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        'user'
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'user'
    ];

    /**
     * Include Author
     *
     * @return League\Fractal\ItemResource
     */
    public function includeUser(array $reply)
    {
        if (isset($reply['user'])) {
            $user = $reply['user'];

            return $this->item($user, new UserTransformer);
        }

        return $this->item([
            'id' => (int) $reply['user_id'],
            'uri' => '/users/' . $reply['user_id']
        ], function ($data) { return $data; });
    }

}
