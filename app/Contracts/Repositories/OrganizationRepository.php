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
     * Add member to an organization
     *
     * @param array $input
     * @param int $organization_id
     *
     * @return array
     */
    public function addMember(array $input, $organization_id);

    /**
     * Add organization member contact
     *
     * @param array $input
     * @param int $organization_id
     * @param int $user_id
     *
     * @return array
     */
    public function addContact(array $input, $organization_id, $user_id);

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
    public function updateContact(array $input, $organization_id, $user_id, $contact_id);

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

    /**
     * Get members of an organization
     *
     * @param int $organization_id
     *
     * @return array
     */
    public function getMembers($organization_id);

    /**
     * Get organization member
     *
     * @param int $organization_id
     * @param int $user_id
     *
     * @return array
     */
    public function getMember($organization_id, $user_id);

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
     * Get list of organizations, optionally filtered by user or url
     *
     * @param string $url
     *
     * @return array
     */
    public function all($url = false);

    /**
     * Check if user is a member of an organization
     *
     * @param int $user_id
     * @param int $org_id
     *
     * @return bool
     */
    public function isMember($user_id, $org_id);

    /**
     * Check if invite token is correct for given user
     *
     * @param int $user_id
     * @param string $invite_token
     *
     * @return bool
     */
    public function testMemberInviteToken($member_id, $invite_token);


}
