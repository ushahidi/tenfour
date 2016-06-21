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
     * @return bool
     */
    public function authorize()
    {
        // Admin has full permissions
        if ($this->isAdmin()) {
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
        return [
            'name' => 'required',
            'url'  => 'required'
        ];
    }
}
