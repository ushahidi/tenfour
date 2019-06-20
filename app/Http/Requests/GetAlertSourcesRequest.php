<?php

namespace TenFour\Http\Requests;

use Dingo\Api\Http\FormRequest;
use TenFour\Traits\UserAccess;

class GetAlertSourcesRequest extends FormRequest
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

        if ($this->user()->isMember($this->route('organization'))) {
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