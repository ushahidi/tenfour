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

            // If user is not an admin/owner, filter to just their rollcalls
            // @todo find a better home for this?
            if (!in_array($org_role, ['admin', 'owner'])) {
                $this->merge([
                    'recipient_id' => $this->auth->user()['id']
                ]);
            }

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
            'owner', 'admin', 'member'
        ];
    }
}
