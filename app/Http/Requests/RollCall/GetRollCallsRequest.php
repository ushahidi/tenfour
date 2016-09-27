<?php

namespace RollCall\Http\Requests\RollCall;

class GetRollCallsRequest extends GetRollCallRequest
{
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
        if ($this->query('org_id')) {
            if ($this->isOrganizationOwner($this->query('org_id'))) {
                return true;
            }

            if ($this->isOrganizationAdmin($this->query('org_id'))) {
                return true;
            }
        }

        return false;
    }
}
