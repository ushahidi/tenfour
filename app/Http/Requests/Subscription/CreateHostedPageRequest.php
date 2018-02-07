<?php

namespace TenFour\Http\Requests\Subscription;

use Dingo\Api\Http\FormRequest;
use TenFour\Traits\UserAccess;

class CreateHostedPageRequest extends GetSubscriptionRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'organization_id'   => 'required',
            'callback'          => 'required',
        ];
    }
}
