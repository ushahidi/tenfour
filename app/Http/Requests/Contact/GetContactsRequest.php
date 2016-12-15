<?php

namespace RollCall\Http\Requests\Contact;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class GetContactsRequest extends FormRequest
{
    use UserAccess;
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // admin can list contacts
        if ($this->isAdmin()) {
            return true;
        }

        //return false;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
