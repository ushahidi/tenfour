<?php

namespace TenFour\Http\Requests\Person;

use TenFour\Traits\UserAccess;

class AddPersonRequest extends UpdatePersonRequest
{
    use UserAccess;

    const MAX_PERSONS_IN_FREE_PLAN = 100;

    public function authorize()
    {
        $org_repo = \App::make('TenFour\Contracts\Repositories\OrganizationRepository');
        $org = $org_repo->find($this->route('organization'));

        if ($org['current_subscription']['plan_id'] === config("chargebee.plans.free")
            && $org['user_count'] >= SELF::MAX_PERSONS_IN_FREE_PLAN) {
            return false;
        }

        // An admin, org owner and org admin can add members
        if ($this->user()->isAdmin($this->route('organization'))) {
            return true;
        }

        return false;
    }

    public function rules()
    {
        $rules = parent::rules() + [
            'name' => 'required',
            'password' => 'required|min:8',
        ];

        return $rules;
    }

}
