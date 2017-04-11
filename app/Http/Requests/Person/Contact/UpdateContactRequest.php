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

        $rules['type']    = 'in:phone,email';
        $rules['contact'] = 'unique:contacts,contact,' . $this->request->get('id');

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
