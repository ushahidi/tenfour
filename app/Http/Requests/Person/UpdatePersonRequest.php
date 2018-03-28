<?php

namespace TenFour\Http\Requests\Person;

use Dingo\Api\Http\FormRequest;
use TenFour\Traits\UserAccess;
use App;

class UpdatePersonRequest extends FormRequest
{
    use UserAccess;

    public function authorize()
    {
        $role = App::make('TenFour\Contracts\Repositories\PersonRepository')
                 ->getMemberRole($this->route('organization'), $this->route('person'));

        // A non-admin cannot update a role
        if (!$this->user()->isAdmin($this->route('organization')) &&
            $this->get('role') &&
            $this->get('role') !== $this->user()->role) {
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
            'role' => 'in:responder,admin,owner,author,viewer',
            'password' => 'min:8',
            'person_type' => 'in:member,user,external',
            'name' => 'required',
            '_input_image' => 'input_image',
        ];

    }
}
