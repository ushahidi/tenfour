<?php

namespace RollCall\Http\Requests\Rollcall;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;
use App;

class CreateRollCallRequest extends FormRequest
{
    use UserAccess;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Admin has full permissions
        if ($this->isAdmin()) {
            return true;
        }

        $rollcall = App::make('RollCall\Contracts\Repositories\RollCallRepository')
                 ->find($this->route('rollcall'));

        $organization = $rollcall->organization_id;
                 
        // An organization admin can send rollcalls
        if ($this->isOrganizationAdmin($organization)) {
            return true;
        }

        if ($this->isMember($organization)) {
            return true;
        }

        if ($this->isOwner($organization)) {
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
            'message'         => 'required',
            'contact_id'      => 'required',
            'organization_id' => 'required'
        ];
    }
}
