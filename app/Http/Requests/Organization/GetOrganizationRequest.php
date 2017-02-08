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
        // If a person_id is provided by a non-admin user,
        // check that it matches the current user's id
        if ($this->route('person') && $this->isSelf($this->route('person'))) {
            return true;
        }

        // An org owner/ admin can view an organization
        //
        // Temporary access given to members to view organization and people
        // See: https://github.com/ushahidi/RollCall/issues/343
        // @TODO remove this when user roles/permissions are in place
        if ($this->user()->isMember($this->route('organization'))) {
            return true;
        }
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
}
