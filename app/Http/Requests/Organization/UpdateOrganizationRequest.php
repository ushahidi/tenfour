<?php

namespace RollCall\Http\Requests\Organization;


class UpdateOrganizationRequest extends GetOrganizationRequest
{
    public function rules()
    {
        return [
            'name' => 'required',
            'subdomain'  => 'required',
        ];
    }

    protected function getAllowedOrgRoles()
    {
        return [
            'owner'
        ];
    }
}
