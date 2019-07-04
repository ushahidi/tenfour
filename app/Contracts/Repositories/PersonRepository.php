<?php
namespace TenFour\Contracts\Repositories;

interface PersonRepository extends OrgCrudRepository
{

    public function all($organization_id, $offset = 0, $limit = 0, $filter = null);

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

    /**
     * Get the admins for an organization
     *
     * @param int $organization_id
     *
     * @return array
     */
    public function getAdmins($organization_id);

    /**
     * Find a user using her email address and her org's subdomain
     *
     * @param string $email
     * @param string $subdomain
     *
     * @return object
     */
    public function findByEmailAndSubdomain($email, $subdomain);

    /**
     * Find a user using her source and source_id (e.g. an LDAP dn)
     *
     * @param string $source
     * @param string $source_id
     *
     * @return object
     */
    public function findBySource($organization_id, $source, $source_id);
}
