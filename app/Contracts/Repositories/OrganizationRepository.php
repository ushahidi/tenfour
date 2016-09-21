<?php
namespace RollCall\Contracts\Repositories;

interface OrganizationRepository extends CrudRepository
{
    /**
     * Get role of member in an organization
     *
     * @param int $organization_id
     * @param int $user_id
     *
     * @return string
     */
    public function getMemberRole($organization_id, $user_id);

    /**
     * Add members to an organization
     *
     * @param array $input
     * @param int $organization_id
     *
     * @return array
     */
    public function addMembers(array $input, $organization_id);

    /**
     * Delete members from an organization
     *
     * @param array $input
     * @param int $organization_id
     *
     * @return array
     */
    public function deleteMembers(array $input, $organization_id);

    /**
     * Filter list of organizations by user id
     *
     * @param int $user_id
     *
     * @return array
     */
    public function filterByUserId($user_id);
}
