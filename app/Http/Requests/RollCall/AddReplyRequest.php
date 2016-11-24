<?php

namespace RollCall\Http\Requests\RollCall;

use App;
use Validator;

class AddReplyRequest extends GetRollCallRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'message' => 'required|string',
        ];
    }

    protected function getAllowedOrgRoles()
    {
        return [
            'owner', 'admin', 'member'
        ];
    }
}
