<?php

namespace TenFour\Http\Requests\Organization;

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
            'owner'             => 'required',
            'email'             => 'required|email',
            'password'          => 'required|min:8',
            'subdomain'         => 'required|alpha_dash|reserved_word',
            'email'             => 'required|exists:unverified_addresses,address',
            'verification_code' => 'required|exists:unverified_addresses,code'
        ];
    }

    public function messages()
    {
        return [
            'subdomain.reserved_word' => 'The subdomain is reserved. Please choose another name'
        ];
    }
}
