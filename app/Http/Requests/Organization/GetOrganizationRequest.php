<?php

namespace RollCall\Http\Requests\Organization;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class GetOrganizationRequest extends FormRequest
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

        // If a person_id is provided by a non-admin user,
        // check that it matches the current user's id
        if ($this->route('person') && $this->isSelf($this->route('person'))) {
            return true;
        }

        $org_role = $this->getOrganizationRole($this->route('organization'));

        // An org owner/ admin can view an organization
        return in_array($org_role, $this->getAllowedOrgRoles());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    protected function getAllowedOrgRoles()
    {
        return [
            'owner', 'admin',

            // Temporary access given to members to view organization and people
            // See: https://github.com/ushahidi/RollCall/issues/343
            // @TODO remove this when user roles/permissions are in place
            'member'

        ];
    }
}
