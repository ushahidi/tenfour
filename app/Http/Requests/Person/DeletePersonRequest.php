<?php

namespace RollCall\Http\Requests\Person;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class DeletePersonRequest extends FormRequest
{
    use UserAccess;

    public function authorize()
    {
        // Users cannot delete themselves
        if ($this->route('person') && $this->isSelf($this->route('person'))) {
            return false;
        }

        // @todo No one can delete the org owner

        // An org admin can delete members
        if ($this->user()->isAdmin($this->route('organization'))) {
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
