<?php

namespace RollCall\Http\Requests\Person\Contact;

use RollCall\Http\Requests\Person\UpdatePersonRequest;
use Illuminate\Validation\Rule;

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

        $rules['type']    = 'in:phone,email';

        $rules['contact'] .= '|'. Rule::unique('contacts')
            ->ignore($this->request->get('id'), 'id')
            ->where('organization_id', $this->request->get('organization_id'));

        return $rules;
    }

    public function messages()
    {
        if ($this->input('type') === 'phone') {
            return [
                'contact.unique' => 'Phone number already in use, choose a different one'
            ];
        } elseif ($this->input('type') === 'email') {
            return [
                'contact.unique' => 'Email already in use, choose a different one'
            ];
        }

    }

}
