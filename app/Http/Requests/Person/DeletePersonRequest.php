<?php

namespace TenFour\Http\Requests\Person;

use Dingo\Api\Http\FormRequest;
use TenFour\Traits\UserAccess;
use App;

class DeletePersonRequest extends FormRequest
{
    use UserAccess;

    public function authorize()
    {
        $role = App::make('TenFour\Contracts\Repositories\PersonRepository')
                 ->getMemberRole($this->route('organization'), $this->route('person'));

        // Users can delete themselves
        if ($this->route('person') && $this->isSelf($this->route('person')) && $role !== 'owner') {
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
            //
        ];
    }
}
