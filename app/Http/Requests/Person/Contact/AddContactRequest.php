<?php

namespace TenFour\Http\Requests\Person\Contact;

class AddContactRequest extends UpdateContactRequest
{
    public function rules()
    {
        $rules = parent::rules();

        $rules['contact'] .= '|required';
        $rules['type'] .= '|required';

        return $rules;
    }
}
