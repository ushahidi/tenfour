<?php

namespace RollCall\Http\Requests\Person\Contact;

use RollCall\Http\Requests\Person\UpdatePersonRequest;
use Illuminate\Validation\Rule;

class UpdateContactRequest extends UpdatePersonRequest
{
    public function rules()
    {
        $rules = [];

        $rules['type'] = 'in:phone,email';

        if ($this->input('type') === 'phone') {
            $rules['contact'] = 'phone_number';
        } elseif ($this->input('type') === 'email') {
            $rules['contact'] = 'email';
        }

        $rules['contact'] .= '|'. Rule::unique('contacts')
                            ->ignore($this->route('contact'))
                            ->where('organization_id', $this->route('organization'));

        return $rules;
    }

    public function messages()
    {
        $messages = [];

        if ($this->input('type') === 'phone') {
            $messages['contact.unique'] = 'Phone number already in use, choose a different one';
        } elseif ($this->input('type') === 'email') {
            $messages['contact.unique'] = 'Email already in use, choose a different one';
        }

        return $messages;
    }
}
