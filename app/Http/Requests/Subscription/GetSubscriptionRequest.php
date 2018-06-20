<?php

namespace TenFour\Http\Requests\Subscription;

use TenFour\Models\Subscription;

use Dingo\Api\Http\FormRequest;
use TenFour\Traits\UserAccess;

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
        if (!$this->user()->isOwner($this->route('organization'))) {
            return false;
        }

        if ($this->route('subscription')) {
            $subscription = Subscription::findOrFail($this->route('subscription'));

            if ($subscription->organization_id !== (int)$this->route('organization')) {
                return false;
            }  
        }

        return true;
    }

    public function rules()
    {
        return [];
    }
}
