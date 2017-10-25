<?php
namespace RollCall\Http\Transformers;

use League\Fractal\TransformerAbstract;

class GroupTransformer extends TransformerAbstract
{
    public function transform(array $group)
    {
        $group['id'] =  (int) $group['id'];
        $group['uri'] = 'organizations/' . $group['organization_id'] . '/groups/' . $group['id'];

        unset($group['organization_id']);

        return $group;
    }
}