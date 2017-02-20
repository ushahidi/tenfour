<?php

namespace RollCall\Http\Requests\Person\Contact;

use RollCall\Http\Requests\Person\UpdatePersonRequest;

class UpdateContactRequest extends UpdatePersonRequest
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
