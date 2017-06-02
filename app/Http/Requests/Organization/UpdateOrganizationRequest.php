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
        // An org owner/ admin can update an organization
        if ($this->user()->isAdmin($this->route('organization'))) {
            return true;
        }
    }

    public function rules()
    {
        return [
            'name'      => 'required',
            'subdomain' => 'required|alpha_dash|reserved_word',
        ];
    }

    public function messages()
    {
        return [
            'subdomain.reserved' => 'The name is reserved, please use another name'
        ];
    }
}
