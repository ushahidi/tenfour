<?php

namespace RollCall\Http\Requests\Person;


class UpdatePersonRequest extends AddPersonRequest
{
    public function rules()
    {
        return [
            'role' => 'in:member,admin,owner',
        ];

    }
}
