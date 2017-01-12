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
        $organization_id = $this->route('organization');
        $user_id = $this->route('person');

        $org_repo = App::make('RollCall\Contracts\Repositories\OrganizationRepository');
        $member_role = $org_repo->getMemberRole($organization_id, $user_id);

        // Person with owner role cannot be deleted
        if ($member_role == 'owner') {
            return false;
        }

        // An admin, org owner and org admin can delete members
        if ($this->isAdmin()) {
            return true;
        }

        $org_role = $this->getOrganizationRole($organization_id);

        if ($org_role == 'owner') {
            return true;
        }

        if ($org_role == 'admin') {
            // Admin can only delete users with 'member' or 'admin' role
            if ($member_role === 'owner') {
                return false;
            }

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
