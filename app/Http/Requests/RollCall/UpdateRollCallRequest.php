<?php

namespace RollCall\Http\Requests\RollCall;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class UpdateRollCallRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Admin has full access
        if ($this->isAdmin()) {
            return true;
        }

        $rollCall = App::make('RollCall\Contracts\Repositories\RollCallRepository')
                 ->find($this->route('rollcall'));

        $org_role = $this->getOrganizationRole($rollCall['organization_id']);

        return in_array($org_role, $this->getAllowedOrgRoles());
    }

    protected function getAllowedOrgRoles()
    {
        return [
            'owner', 'admin'
        ];
    }

    public function rules()
    {
        return [
            'sent'   => 'integer',
            'status' => 'in:pending,received,expired,cancelled,failed',
            'recipients.*.id'   => 'exists:users,id'
        ];
    }
}
