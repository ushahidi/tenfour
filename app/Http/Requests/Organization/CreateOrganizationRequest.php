<?php

namespace RollCall\Http\Requests\Organization;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class CreateOrganizationRequest extends FormRequest
{
    use UserAccess;
    /**
     * Determine if the user is authorized to make this request.
     *
     *  @return bool
     */
    public function authorize()
    {
        // Admin can create an organization for any user
        if ($this->isAdmin()) {
            return true;
        }

        // If a user_id is provided by a non-admin user,
        // check that it matches the current user's id
        if ($this->has('user_id')) {
            return $this->isSelf($this->user_id);
        }

        // Check that at least the user creating
        // an organization is a registered user
        if ($this->auth->user()->id) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'    => 'required',
            'url'     => 'required',
            'user_id' => 'exists:users,id',
        ];
    }
}
