<?php

namespace TenFour\Http\Requests\Group;

use Dingo\Api\Http\FormRequest;
use TenFour\Traits\UserAccess;

class GetGroupsRequest extends FormRequest
{
    use UserAccess;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // An org owner/admin/author can view a list of groups in an organization

        if ($this->user()->isAdmin($this->route('organization'))) {
            return true;
        }

        if (($this->user()->role === 'author' || $this->user()->role === 'viewer') &&
            $this->user()->organization_id == $this->route('organization')) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
