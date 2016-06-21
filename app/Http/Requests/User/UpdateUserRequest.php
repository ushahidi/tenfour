<?php

namespace RollCall\Http\Requests\User;

use RollCall\Traits\UserAccess;

class UpdateUserRequest extends CreateUserRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Admin has access
        if ($this->isAdmin()) {
            return true;
        }

        // A user can edit their own details
        if ($this->isSelf($this->route('user'))) {
            return true;
        }

        return false;
    }
}
