<?php

namespace RollCall\Http\Requests\Person\Contact;

use RollCall\Http\Requests\Person\UpdatePersonRequest;

class UpdateContactRequest extends UpdatePersonRequest
{
    public function rules()
    {
        $rules = [];

        if ($this->input('type') === 'phone') {
            $rules['contact'] = 'phone_number';
        } elseif ($this->input('type') === 'email') {
            $rules['contact'] = 'email';
        }

        $rules['type'] = 'in:phone,email';

        return $rules;
    }

}
