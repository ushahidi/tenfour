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
        // Anyone should be able to create an organization
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'organization_name' => 'required',
            'subdomain'         => 'required|alpha_dash',
            'email'             => 'required|email',
            'password'          => 'required|min:8'
            'owner'    => 'required',
        ];
    }
}
