<?php

namespace RollCall\Http\Requests\Organization;


class UpdateOrganizationMemberRequest extends UpdateOrganizationRequest
{
    public function rules()
    {
        return [
            'role' => 'required|in:member,admin,owner',
        ];
    }
}
