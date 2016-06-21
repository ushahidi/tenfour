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

        // A user is an organization admin
        if ($this->isOrganizationAdmin($this->route('organization'))) {
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
        return [];
    }
}
