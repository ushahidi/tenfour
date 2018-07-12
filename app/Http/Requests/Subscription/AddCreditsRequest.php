<?php

namespace TenFour\Http\Requests\Subscription;

use Dingo\Api\Http\FormRequest;
use TenFour\Traits\UserAccess;

class AddCreditsRequest extends GetSubscriptionRequest
{
    public function rules()
    {
        return [
            'quantity' => 'required'
        ];
    }
}
