<?php

namespace RollCall\Http\Requests\Organization;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class AddMemberRequest extends FormRequest
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
            $member = $this->all();

            if (isset($member['role']) && $member['role'] != 'member') {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @todo Merge rules from `CreateUserRequest`
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'role' => 'in:member,admin',
        ];
    }
}
