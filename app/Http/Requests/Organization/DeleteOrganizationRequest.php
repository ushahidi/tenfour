<?php

namespace RollCall\Http\Requests\Organization;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class DeleteOrganizationRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Only an org owner can delete the org
        if ($this->user()->isOwner($this->route('organization'))) {
            return true;
        }

        return false;
    }

    public function rules()
    {
        return [];
    }
}
