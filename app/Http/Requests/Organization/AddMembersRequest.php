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

        $org_role = $this->getOrganizationRole($this->route('organization'));

        if ($org_role == 'owner') {
            return true;
        }

        if ($org_role == 'admin') {
            // Admin can only add members with 'member' role
            if (is_array(head($this->all()))) {
                foreach($this->all() as $member)
                {
                    return $this->orgAdminCanAddMember($member);
                }
            } else {
                return $this->orgAdminCanAddMember($this->all());
            }

            return true;
        }

        return false;
    }

    public function rules()
    {
        $rules = [
            'email' => 'email',
            'role'  => 'in:member,admin',
        ];

        // Validate request with multiple members
        if (is_array(head($this->all()))) {
            $members = [];

            foreach($this->all() as $key => $val)
            {
                $members[$key.'.email'] = $rules['email'];
                $members[$key.'.role'] = $rules['role'];
            }

            return $members;
        }

        // ...else validate request with single member
        return $rules;
    }

    private function orgAdminCanAddMember($member)
    {
        if (isset($member['role']) && $member['role'] != 'member') {
            return false;
        }

        return true;
    }
}
