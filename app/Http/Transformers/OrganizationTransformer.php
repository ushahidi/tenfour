<?php
namespace RollCall\Http\Transformers;

use League\Fractal\TransformerAbstract;

class OrganizationTransformer extends TransformerAbstract
{
    public function transform(array $organization)
    {
        // Format members if they exist
        if (isset($organization['members'])) {
            foreach($organization['members'] as &$member)
            {
                $member['id'] = (int) $member['id'];
                $member['uri'] = '/users/' . $member['id'];
            }
        }

        $organization['id'] = (int) $organization['id'];
        $organization['uri'] = '/organizations/' . $organization['id'];

        return $organization;
    }
}
