<?php

namespace RollCall\Http\Requests\Member\Contact;

use RollCall\Http\Requests\Organization\GetOrganizationRequest;

class UpdateContactRequest extends GetOrganizationRequest
{
    public function rules()
    {
        $rules = [];

        if ($this->input('type') === 'phone') {
            // TODO: Use libphonenumber to validate phone numbers
            $rules['contact'] = 'numeric';
        } elseif ($this->input('type') === 'email') {
            $rules['contact'] = 'email';
        }

        $rules['type'] = 'in:phone,email';

        return $rules;
    }

}
