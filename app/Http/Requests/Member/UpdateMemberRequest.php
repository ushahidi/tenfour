<?php

namespace RollCall\Http\Requests\Member;


class UpdateMemberRequest extends AddMemberRequest
{
    public function rules()
    {
        return [
            'role' => 'in:member,admin,owner',
        ];

    }
}
