<?php

namespace TenFour\Http\Requests\Organization;

use Dingo\Api\Http\FormRequest;

class LookupOrganizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     *  @return bool
     */
    public function authorize()
    {
        // Anyone should be able to lookup an organization
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
            'email'             => 'required|email',
        ];
    }

}
