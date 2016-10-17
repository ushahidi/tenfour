<?php

namespace RollCall\Http\Requests\RollCall;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;
use App;

class CreateRollCallRequest extends FormRequest
{
    use UserAccess;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Admin has full permissions
        if ($this->isAdmin()) {
            return true;
        }

        $org_role = $this->getOrganizationRole($this->input('organization'));

        return in_array($org_role, $this->getAllowedOrgRoles());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'message'         => 'required',
            'organization' => 'required|integer'
        ];
    }

    protected function getAllowedOrgRoles()
    {
        return [
            'owner', 'admin'
        ];
    }
}
