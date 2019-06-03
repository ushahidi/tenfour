<?php
namespace TenFour\Http\Transformers;

use League\Fractal\TransformerAbstract;

class UserLocationTransformer extends TransformerAbstract
{
    public function transform(array $userLocation)
    {
        if (!isset($userLocation['user']) && isset($userLocation['user_id'])) {
            $userLocation['user']['id'] = $userLocation['user_id'];
            unset($userLocation['user_id']);
            $userLocation['user']['id'] = (int) $userLocation['user']['id'];
            $userLocation['user']['uri'] = '/users/' . $userLocation['user']['id'];
        }
        return $userLocation;
    }
}
