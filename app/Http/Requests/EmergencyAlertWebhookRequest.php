<?php

namespace TenFour\Http\Requests;

use Dingo\Api\Http\FormRequest;
use TenFour\Traits\UserAccess;

class EmergencyAlertWebhookRequest extends FormRequest
{
    // use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->getUser() === config('emergency-alerts.webhook.username') &&
            $this->getPassword() ===  config('emergency-alerts.webhook.password')) {
            return true;
        }
        return false;
    }

    public function rules()
    {
        return [
            'feed_id' => 'required',
            'title' => 'required',
            'body' => 'required',
            'country' => 'required',
        ];
    }

}
