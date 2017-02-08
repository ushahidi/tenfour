<?php

namespace RollCall\Http\Requests\User;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class DeleteUserRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // FIXME

        // A user can *not* delete themselves
        if ($this->route('user') === 'me') {
            return false;
        }

        // A user can *not* delete themselves
        if ($this->isSelf($this->route('user'))) {
            return false;
        }

        return false;
    }

    public function rules()
    {
        return [];
    }
}
