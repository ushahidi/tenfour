<?php

namespace RollCall\Http\Requests\Organization;

class AddMemberContactsRequest extends GetOrganizationRequest
{
    public function rules()
    {
        $rules = [
            'type'  => 'in:phone,email',
            'email' => 'email',

            // TODO: Use libphonenumber to validate phone numbers
            'phone' => 'numeric',
        ];

        // Validate request with multiple contacts
        if (is_array(head($this->all()))) {
            $members = [];

            foreach($this->all() as $key => $contact)
            {
                //if ($key.'type' === 'phone')
                if ($contact['type'] == 'phone') {
                    $members[$key.'.contact'] = $rules['phone'];
                } elseif ($contact['type'] == 'email') {
                    $members[$key.'.contact'] = $rules['email'];
                }

                $members[$key.'.type'] = $rules['type'];
            }

            return $members;
        }

        // ...else validate request with single contact

        $member = [];


        if ($this->input['type'] == 'phone') {
            $member['contact'] = $rules['phone'];
        } elseif ($this->input['type'] == 'email') {
            $member['contact'] = $rules['email'];
        }

        $member['type'] = $rules['type'];

        return $member;
    }
}
