<?php

namespace RollCall\Http\Requests\Organization;

use Dingo\Api\Http\FormRequest;

class CreateOrganizationRequest extends UpdateOrganizationRequest
{
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
        return parent::rules() + [
            'owner'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8'
        ];
    }
}
