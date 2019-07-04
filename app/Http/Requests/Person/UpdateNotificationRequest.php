<?php

namespace TenFour\Http\Requests\Person;

use Dingo\Api\Http\FormRequest;
use TenFour\Traits\UserAccess;
use App;

class UpdateNotificationRequest extends UpdatePersonRequest
{
    public function rules()
    {
        return [
        ];

    }
}
