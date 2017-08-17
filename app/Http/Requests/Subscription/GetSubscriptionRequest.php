<?php

namespace RollCall\Http\Requests\Subscription;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class GetSubscriptionRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     *  @return bool
     */
    public function authorize()
    {
        if ($this->user()->isOwner($this->route('organization'))) {
            return true;
        }

        return false;
    }

    public function rules()
    {
        return [];
    }
}
