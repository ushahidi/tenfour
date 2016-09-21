<?php

namespace RollCall\Http\Requests\Organization;

class GetOrganizationsRequest extends GetOrganizationRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Admin can list all organizations
        if ($this->isAdmin()) {
            return true;
        }

        // A user can list their own orgs;
        if ($this->isSelf($this->query('user_id'))) {
            return true;
        }

        return false;
    }
}
