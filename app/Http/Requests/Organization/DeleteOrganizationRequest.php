<?php

namespace RollCall\Http\Requests\Organization;

class DeleteOrganizationRequest extends GetOrganizationRequest
{
    public function authorize()
    {
        // Admin has full access
        if ($this->isAdmin()) {
            return true;
        }

        // An org owner can delete an organization
        if ($this->isOrganizationOwner($this->route('organization'))) {
            return true;
        }

        return false;
    }
}
