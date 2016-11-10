<?php

namespace RollCall\Http\Requests\RollCall;

class AddContactsRequest extends GetRollCallRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = 'required|integer|org_contact:'.$this->route('rollcall');

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
