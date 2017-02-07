<?php
namespace RollCall\Contracts\Repositories;

interface OrganizationRepository extends CrudRepository
{
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
     * Get list of organizations, optionally filtered by user or subdomain
     *
     * @param string $subdomain
     *
     * @return array
     */
    public function all($subdomain = false);

}
