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
        // Only admin can list organizations
        if ($this->isAdmin()) {
            return true;
        }

        return false;
    }
}
