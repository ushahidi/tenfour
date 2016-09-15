<?php
namespace RollCall\Http\Transformers;

use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(array $user)
    {
        return [
            'id'      => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email']            
        ];
    }
}