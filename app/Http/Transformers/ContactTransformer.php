<?php
namespace RollCall\Http\Transformers;

use League\Fractal\TransformerAbstract;

class ContactTransformer extends TransformerAbstract
{
    public function transform(array $contact)
    {
        return [
            //'id'      => (int) $contact['id'],
            'receive' => $contact['can_receive'],
            'type' => $contact['type'],
            'contact' => $contact['contact'],
            'user' => [
                'id'  => (int) $contact['user_id'],
                'uri' => '/users/' . $contact['user_id'],
            ],           
        ];
    }
}