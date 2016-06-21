<?php
namespace RollCall\Contracts\Repositories;

interface OrganizationRepository extends CrudRepository
{
    /**
     * Check an admin exists in an organization
     *
     * @param int $organization_id
     * @param int $user_id
     *
     * @return bool
     */
    public function adminExists($organization_id, $user_id);
}
