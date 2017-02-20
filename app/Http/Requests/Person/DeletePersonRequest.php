<?php

namespace RollCall\Http\Requests\Person;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;
use App;

class DeletePersonRequest extends FormRequest
{
    use UserAccess;

    public function authorize()
    {
        // Users cannot delete themselves
        if ($this->route('person') && $this->isSelf($this->route('person'))) {
            return false;
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
            //
        ];
    }
}
