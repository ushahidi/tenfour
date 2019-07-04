<?php

namespace TenFour\Http\Requests\Person\Contact;

class AddContactRequest extends UpdateContactRequest
{
    public function rules()
    {
        $rules = parent::rules();

        if (!isset($rules['contact'])) {
            $rules['contact'] = '';
        }

        $rules['contact'] .= '|required';
        $rules['type'] .= '|required';

        return $rules;
    }
}
