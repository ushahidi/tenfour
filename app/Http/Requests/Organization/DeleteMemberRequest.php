<?php

namespace RollCall\Http\Requests\Organization;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;
use App;

class DeleteMemberRequest extends FormRequest
{
    use UserAccess;

    public function authorize()
    {
        $organization_id = $this->route('organization');
        $user_id = $this->route('member');

        $org_repo = App::make('RollCall\Contracts\Repositories\OrganizationRepository');
        $member_role = $org_repo->getMemberRole($organization_id, $user_id);

        // Member with owner role cannot be deleted
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
            // Admin can only delete users with 'member' role
            if ($member_role != 'member') {
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
