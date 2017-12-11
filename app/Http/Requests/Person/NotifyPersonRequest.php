<?php

namespace RollCall\Http\Requests\Person;

class NotifyPersonRequest extends GetPeopleRequest
{

    public function rules()
    {
        return [
            'message' => 'required',
        ];
    }

}
