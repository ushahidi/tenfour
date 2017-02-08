<?php

namespace RollCall\Http\Requests\User;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class GetUserRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // A user can access their own details
        if ($this->route('user') === 'me') {
            return true;
        }

        if ($this->isSelf($this->route('user'))) {
            return true;
        }

        return false;
    }

    public function rules()
    {
        return [];
    }
}
