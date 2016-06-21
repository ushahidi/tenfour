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
        // Only admin can list users
        if ($this->isAdmin()) {
            return true;
        }

        return false;
    }
}
