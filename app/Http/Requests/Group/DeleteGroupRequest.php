<?php

namespace RollCall\Http\Requests\Group;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class DeleteGroupRequest extends FormRequest
{
    use UserAccess;
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // An org admin can delete groups
        if ($this->user()->isAdmin($this->route('organization'))) {
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
