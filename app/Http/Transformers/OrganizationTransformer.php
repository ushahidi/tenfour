<?php
namespace RollCall\Http\Transformers;

use League\Fractal\TransformerAbstract;

class OrganizationTransformer extends TransformerAbstract
{
    public function transform(array $organization)
    {
        if (isset($organization['user_id'])) {
            $organization['user']['id'] = $organization['user_id'];
            unset($organization['user_id']);
        }

        if (isset($organization['role'])) {
            $organization['user']['role'] = $organization['role'];
            unset($organization['role']);
        }

        // Format members if they exist
        if (isset($organization['members'])) {
            foreach($organization['members'] as &$member)
            {
                $member['id'] = (int) $member['id'];
                $member['uri'] = '/users/' . $member['id'];

                unset($member['pivot']);
            }
        }

        $organization['id'] = (int) $organization['id'];
        $organization['uri'] = '/organizations/' . $organization['id'];

        return $organization;
    }
}
