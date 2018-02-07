<?php

namespace TenFour\Http\Requests\Person;

class NotifyPersonRequest extends GetPeopleRequest
{

    public function rules()
    {
        return [
            'message' => 'required',
        ];
    }

}
