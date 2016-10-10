<?php

namespace RollCall\Http\Requests\Organization;


class UpdateOrganizationRequest extends GetOrganizationRequest
{
    public function rules()
    {
        $rules = [
            'name' => 'required',
            'url'  => 'required',
        ];

        if ($this->has('members')) {
            foreach($this->input('members') as $key => $val)
            {
                $rules['members.'.$key.'.role'] = 'required|in:member,admin,owner';
            }
        }

        return $rules;
    }

    protected function getAllowedOrgRoles()
    {
        return [
            'owner'
        ];
    }
}
