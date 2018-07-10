<?php

namespace TenFour\Http\Requests\Person;

use Dingo\Api\Http\FormRequest;
use TenFour\Traits\UserAccess;

class DeviceTokenRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // A user can access their own details
        return $this->isSelf($this->route('person'));
    }

    public function rules()
    {
        return [
            //
        ];
    }

}
