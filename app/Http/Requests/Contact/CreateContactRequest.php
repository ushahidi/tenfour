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
        // An organization admin has access to all contacts for their organization
        if ($this->isOrganizationAdmin($this->route('organization'))) {
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
            'user_id'      => 'required',
            'can_receive'  => 'required',
            'type'         => 'required',
            'contact'      => 'required'
        ];
    }
}
