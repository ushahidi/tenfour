<?php

namespace RollCall\Http\Requests\Organization;

use RollCall\Traits\UserAccess;

class UpdateOrganizationRequest extends CreateOrganizationRequest
{
    use UserAccess;
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Admin has access
        if ($this->isAdmin()) {
            return true;
        }

        // A user is an organization admin
        if ($this->isOrganizationAdmin($this->route('organization'))) {
            return true;
        }

        return false;
    }
}
