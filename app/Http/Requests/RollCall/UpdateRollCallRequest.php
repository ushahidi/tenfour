<?php

namespace RollCall\Http\Requests\RollCall;

class UpdateRollCallRequest extends GetRollCallRequest
{
    public function rules()
    {
        return [
            'sent'   => 'integer',
            'status' => 'in:pending,received,expired,cancelled,failed',
        ];
    }
}
