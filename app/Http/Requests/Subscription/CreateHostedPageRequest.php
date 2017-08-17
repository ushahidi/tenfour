<?php

namespace RollCall\Http\Requests\Subscription;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

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
