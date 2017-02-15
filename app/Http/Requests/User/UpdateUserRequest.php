<?php

namespace RollCall\Http\Requests\User;

use RollCall\Traits\UserAccess;
use Dingo\Api\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'password' => 'min:8',
            'person_type' => 'in:member,user,external'
        ];
    }
}
