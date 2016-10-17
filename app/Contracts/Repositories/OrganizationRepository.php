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
     * List members to an organization
     *
     * @param int $organization_id
     *
     * @return array
     */
    public function getMembers($organization_id);


    /**
     * Delete member from an organization
     *
     * @param int $organization_id
     * @param int $user_id
     *
     * @return array
     */
    public function deleteMember($organization_id, $user_id);

    /**
     * Filter list of organizations by user id
     *
     * @param int $user_id
     *
     * @return array
     */
    public function filterByUserId($user_id);

    /**
     * Check if user is a member of an organization
     *
     * @param int $user_id
     * @param int $org_id
     *
     * @return bool
     */
    public function isMember($user_id, $org_id);
}
