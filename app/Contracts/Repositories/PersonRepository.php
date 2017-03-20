<?php
namespace RollCall\Contracts\Repositories;

interface PersonRepository extends OrgCrudRepository
{

    public function all($organization_id, $offset = 0, $limit = 0);

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
     * Check if invite token is correct for given user
     *
     * @param int $user_id
     * @param string $invite_token
     *
     * @return bool
     */
    public function testMemberInviteToken($user_id, $invite_token);

    /**
     * Add organization member contact
     *
     * @param array $input
     * @param int $organization_id
     * @param int $user_id
     *
     * @return array
     */
    public function addContact($organization_id, $user_id, array $input);

    /**
     * Update organization member contact
     *
     * @param array $input
     * @param int $organization_id
     * @param int $user_id
     * @param int $contact_id
     *
     * @return array
     */
    public function updateContact($organization_id, $user_id, array $input, $contact_id);

    /**
     * Delete organization member contact
     *
     * @param int $organization_id
     * @param int $user_id
     * @param int $contact_id
     *
     * @return array
     */
    public function deleteContact($organization_id, $user_id, $contact_id);


}
