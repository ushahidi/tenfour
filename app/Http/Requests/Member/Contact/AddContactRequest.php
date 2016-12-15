<?php

namespace RollCall\Http\Requests\Member\Contact;

class AddContactRequest extends UpdateContactRequest
{
    public function rules()
    {
        $rules = parent::rules();

        $rules['contact'] .= '|required';

        return $rules;
    }
}
