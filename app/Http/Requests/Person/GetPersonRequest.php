<?php

namespace RollCall\Http\Requests\Person;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class GetPersonRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        clock()->startEvent('person_authorise_event', 'Person::authorise');
        // An org owner/ admin can view an organization
        //
        // Temporary access given to members to view organization and people
        // See: https://github.com/ushahidi/RollCall/issues/343
        // @TODO remove this when user roles/permissions are in place
        if ($this->user()->isMember($this->route('organization'))) {
            clock()->endEvent('person_authorise_event');
            return true;
        }

        // A user can access their own details
        if ($this->isSelf($this->route('person'))) {
          clock()->endEvent('person_authorise_event');
            return true;
        }
        clock()->endEvent('person_authorise_event');
        return false;
    }

    public function rules()
    {
        return [
            //
        ];
    }

}
