<?php

namespace TenFour\Http\Requests\Group;

use Dingo\Api\Http\FormRequest;
use TenFour\Traits\UserAccess;
use App;

class UpdateGroupRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->user()->isAdmin($this->route('organization'))) {
            return true;
        }

        return false;

    }

    public function rules()
    {
        return [
            'name' => 'required',
            'members.*.id'   => 'exists:users,id'
        ];
    }
}
