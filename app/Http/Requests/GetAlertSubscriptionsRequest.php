<?php

namespace TenFour\Http\Requests\EmergencyAlerts;

use Dingo\Api\Http\FormRequest;
use TenFour\Traits\UserAccess;

class GetAlertSubscriptionsRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (!$this->user()) {
            return false;
        }

        if ($this->user()->isMember($this->query('organization'))) {
            return true;
        }

        return false;
    }

    public function rules()
    {
        return [
            //
        ];
    }

}
