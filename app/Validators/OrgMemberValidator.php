<?php

namespace TenFour\Validators;

use TenFour\Contracts\Repositories\CheckInRepository;
use TenFour\Contracts\Repositories\ContactRepository;
use TenFour\Contracts\Repositories\OrganizationRepository;

class OrgMemberValidator
{
    public function __construct(CheckInRepository $check_in_repo, ContactRepository $contact_repo, OrganizationRepository $org_repo)
    {
        $this->check_in_repo = $check_in_repo;
        $this->contact_repo = $contact_repo;
        $this->org_repo = $org_repo;
    }

    public function validateContact($attr, $value, $params)
    {
        $check_in_id = $params[0];
        $contact_id = $value;

        $check_in = $this->check_in_repo->find($check_in_id);
        $contact = $this->contact_repo->find($contact_id);

        return $this->org_repo->isMember($contact['user_id'], $check_in['organization_id']);
    }
}
