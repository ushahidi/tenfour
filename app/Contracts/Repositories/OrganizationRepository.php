<?php
namespace RollCall\Contracts\Repositories;

interface OrganizationRepository extends CrudRepository
{
    /**
     * Get User by ID 
     *
     * @param int $organization_id
     * @param int $user_id
     *
     * @return bool
     */
    public function getUserById($organization_id, $user_id);
}
