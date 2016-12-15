<?php

namespace RollCall\Http\Requests\Organization;

class DeleteOrganizationRequest extends GetOrganizationRequest
{
    public function rules()
    {
        return [];
    }

    protected function getAllowedOrgRoles()
    {
        return [
            'owner'
        ];
    }
}
