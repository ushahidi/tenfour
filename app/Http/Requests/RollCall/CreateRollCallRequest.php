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

        $org_role = $this->getOrganizationRole($this->input('organization_id'));

        return in_array($org_role, $this->getAllowedOrgRoles());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Workaround until Laravel 5.3
        $recipient_rules = [];
        foreach($this->request->get('recipients') as $key => $val)
        {
            $rules['recipients.'.$key] = 'required|exists:users,id';
        }

        return [
            'message'         => 'required',
            'organization_id' => 'required|integer',
            'recipients'      => 'required',
            // This doesn't work in Laravel 5.1
            // 'recipients.*.id'   => 'required|exists:users,id'
        ] + $recipient_rules;
    }

    protected function getAllowedOrgRoles()
    {
        return [
            'owner', 'admin'
        ];
    }
}
