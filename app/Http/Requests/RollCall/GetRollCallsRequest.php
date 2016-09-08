<?php

namespace RollCall\Http\Requests\RollCall;  

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
