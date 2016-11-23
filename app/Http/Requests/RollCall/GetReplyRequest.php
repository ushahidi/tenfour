<?php

namespace RollCall\Http\Requests\RollCall;

use App;
use Validator;

class GetReplyRequest extends GetRollCallRequest
{
    protected function getAllowedOrgRoles()
    {
        return [
            'owner', 'admin', 'member'
        ];
    }
}
