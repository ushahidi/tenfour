<?php

namespace RollCall\Http\Requests\RollCall;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class GetRollCallsRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Admin has full access
        if ($this->isAdmin()) {
            return true;
        }

        // If filtering by organization check whether user is org owner/ org admin
        if ($this->query('organization')) {
            $org_role = $this->getOrganizationRole($this->query('organization'));

            return in_array($org_role, $this->getAllowedOrgRoles());
        }

        return false;
    }

    public function rules()
    {
        return [
            //
        ];
    }

    protected function getAllowedOrgRoles()
    {
        return [
            'owner', 'admin'
        ];
    }
}
