<?php

namespace RollCall\Http\Requests\User;

use Dingo\Api\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Anyone can register as a user
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'email' => 'required|unique:users|max:255',
            'password' => 'required|min:8',
            'password_confirm' => 'required|same:password',
            'person_type' => 'required|in:member,user,external',
        ];
    }
}
