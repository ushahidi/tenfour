<?php

namespace RollCall\Http\Requests\Contact;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;


class CreateContactRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Admin has full permissions
        if ($this->isAdmin()) {
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
            'user_id'      => 'exists:users,id',
            'type'         => 'required',
            'contact'      => 'required'
        ];
    }
}
