<?php

namespace RollCall\Http\Requests\Person;

use Dingo\Api\Http\FormRequest;

class InviteMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Anyone can be invited as a user
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
            'email' => 'required|max:255',
            'password' => 'required|min:8',
            'password_confirm' => 'required|same:password',
            'invite_token' => 'required',
        ];
    }
}
