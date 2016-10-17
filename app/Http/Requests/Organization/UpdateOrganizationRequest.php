<?php

namespace RollCall\Http\Requests\Organization;


class UpdateOrganizationRequest extends GetOrganizationRequest
{
    public function rules()
    {
        return [
            'name' => 'required',
            'url'  => 'required',
        ];
    }

    protected function getAllowedOrgRoles()
    {
        return [
            'owner'
        ];
    }
}
