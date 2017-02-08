<?php

namespace RollCall\Http\Requests\User;

class GetUsersRequest extends GetUserRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // FIXME

        return false;
    }
}
