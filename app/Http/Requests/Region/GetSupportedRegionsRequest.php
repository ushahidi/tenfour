<?php

namespace TenFour\Http\Requests\Region;

use Dingo\Api\Http\FormRequest;
use TenFour\Traits\UserAccess;

class GetSupportedRegionsRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // An org owner/ admin can view regions supported for their organization
        if ($this->user()->isAdmin($this->route('organization'))) {
            return true;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
