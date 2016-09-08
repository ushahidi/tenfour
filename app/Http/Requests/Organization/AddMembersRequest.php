<?php

namespace RollCall\Http\Requests\Organization;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class AddMembersRequest extends FormRequest
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

            // Admin can only add members with 'member' role
            foreach($this->input('members') as $member)
            {
                if (isset($member['role']) && $member['role'] !== 'member') {
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
            $rules['members.'.$key.'.id'] = 'required|exists:users,id';
            $rules['members.'.$key.'.role'] = 'in:member,admin';
        }

        return $rules;
           
    }
}
