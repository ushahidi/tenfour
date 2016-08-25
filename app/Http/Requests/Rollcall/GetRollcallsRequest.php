<?php

namespace RollCall\Http\Requests\Rollcall;  

class GetRollCallsRequest extends GetRollCallRequest
{
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
    }
}
