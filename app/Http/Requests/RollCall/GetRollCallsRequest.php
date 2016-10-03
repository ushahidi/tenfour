<?php

namespace RollCall\Http\Requests\RollCall;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class GetRollCallsRequest extends FormRequest
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

        // If filtering by organization check whether user is org owner/ org admin
        if ($this->query('organization')) {
            if ($this->isOrganizationOwner($this->query('organization'))) {
                return true;
            }

            if ($this->isOrganizationAdmin($this->query('organization'))) {
                return true;
            }
        }

        return false;
    }

    public function rules()
    {
        return [
            //
        ];
    }
}
