<?php

namespace RollCall\Http\Requests\Person;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class UpdatePersonRequest extends FormRequest
{
    use UserAccess;

    public function authorize()
    {
        // @todo Only the owner can update themselves

        // Users can update themselves
        if ($this->isSelf($this->route('person'))) {
            return true;
        }

        // An org admin can delete members
        if ($this->user()->isAdmin($this->route('organization'))) {
            return true;
        }

        return false;
    }

    public function rules()
    {
        return [
            'role' => 'in:member,admin,owner',
        ];

    }
}
