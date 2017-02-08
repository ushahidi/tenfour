<?php

namespace RollCall\Http\Requests\Person;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;
use App;

class UpdatePersonRequest extends FormRequest
{
    use UserAccess;

    public function authorize()
    {
        // Users can update themselves
        if ($this->isSelf($this->route('person'))) {
            return true;
        }

        $role = App::make('RollCall\Contracts\Repositories\PersonRepository')
                 ->getMemberRole($this->route('organization'), $this->route('person'));

        // Only the owner can edit their details
        if ($role === 'owner') {
            return false;
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
