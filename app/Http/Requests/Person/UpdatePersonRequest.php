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
        $role = App::make('RollCall\Contracts\Repositories\PersonRepository')
                 ->getMemberRole($this->route('organization'), $this->route('person'));

        // A member cannot update a role
        if ($this->user()->role === 'member' && $this->get('role')) {
          return false;
        }

        // Users can update themselves
        if ($this->isSelf($this->route('person'))) {
            return true;
        }

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
            'password' => 'min:8',
            'person_type' => 'in:member,user,external',
            'name' => 'required',
            '_input_image' => 'input_image',
        ];

    }
}
