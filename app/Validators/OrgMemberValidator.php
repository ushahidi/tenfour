<?php

namespace RollCall\Validators;

use RollCall\Contracts\Repositories\RollCallRepository;
use RollCall\Contracts\Repositories\ContactRepository;
use RollCall\Contracts\Repositories\OrganizationRepository;

class OrgMemberValidator
{
    public function __construct(RollCallRepository $roll_call_repo, ContactRepository $contact_repo, OrganizationRepository $org_repo)
    {
        $this->roll_call_repo = $roll_call_repo;
        $this->contact_repo = $contact_repo;
        $this->org_repo = $org_repo;
    }

    public function validateContact($attr, $value, $params)
    {
        $roll_call_id = $params[0];
        $contact_id = $value;

        $rollcall = $this->roll_call_repo->find($roll_call_id);
        $contact = $this->contact_repo->find($contact_id);

        return $this->org_repo->isMember($contact['user_id'], $rollcall['organization_id']);
    }
}
