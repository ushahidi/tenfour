<?php

namespace TenFour\Http\Requests\CheckIn;

class AddContactsRequest extends GetCheckInRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = 'required|integer|org_contact:'.$this->route('checkin');

        if (is_array(head($this->all()))) {
            $contacts = [];

            foreach($this->input() as $key => $val)
            {
                $contacts[$key.'.id'] = $rules;
            }

            return $contacts;
        }

        // ...else validate single contact
        return [
            'id' => $rules
        ];
    }
}
