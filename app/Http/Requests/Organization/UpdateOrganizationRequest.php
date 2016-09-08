<?php

namespace RollCall\Http\Requests\Organization;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class UpdateOrganizationRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Admin can update all organizations
        if ($this->isAdmin()) {
            return true;
        }

        // An organization owner can update their own organization
        if ($this->isOrganizationOwner($this->route('organization'))) {
            return true;
        }

        return false;
    }

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
}
