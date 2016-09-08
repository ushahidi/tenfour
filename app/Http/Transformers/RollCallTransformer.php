<?php
namespace RollCall\Http\Transformers;

use League\Fractal\TransformerAbstract;

class RollCallTransformer extends TransformerAbstract
{
    public function transform(array $rollcall)
    {
        return [
            'id'      => (int) $rollcall['id'],
            'message' => $rollcall['message'],
            'organization' => [
                'id'  => (int) $rollcall['organization_id'],
                'uri' => '/organizations/' . $rollcall['organization_id'],
            ],      
            'contact' => [
                'id'  => (int) $rollcall['contact_id'],
                'uri' => '/contacts/' . $rollcall['contact_id'],
            ],
            
        ];
    }
}