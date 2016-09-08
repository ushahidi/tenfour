<?php

namespace RollCall\Http\Requests\Organization;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;
use App;

class DeleteMembersRequest extends FormRequest
{
    use UserAccess;

    public function authorize()
    {
        // An admin, org owner and org admin can add members
        if ($this->isAdmin()) {
            return true;
        }

        $organization_id = $this->route('organization');

    
        if ($this->isOrganizationOwner($organization_id)) {
            return true;
        }

        if ($this->isOrganizationAdmin($organization_id)) {

			$org_repo = App::make('RollCall\Contracts\Repositories\OrganizationRepository');

            // Admin can only delete members with 'member' role
            foreach($this->input('members') as $member)
            {
				$role = $org_repo->getMemberRole($organization_id, $member['id']);
						
                if ($role !== 'member') {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    public function rules()
    {
        $rules = [];

        foreach($this->input('members') as $key => $val)
        {
            $rules['members.'.$key.'.id'] = 'required|int';
        }

        return $rules;
    }
}
