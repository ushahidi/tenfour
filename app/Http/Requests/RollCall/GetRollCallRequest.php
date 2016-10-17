<?php

namespace RollCall\Http\Requests\RollCall;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;
use App;

class GetRollCallRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    protected function getAllowedOrgRoles()
    {
        return [
            'owner', 'admin'
        ];
    }
}
