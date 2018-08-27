<?php
namespace TenFour\Contracts\Repositories;

interface GroupRepository extends OrgCrudRepository
{

    public function all($organization_id, $offset = 0, $limit = 0);

    /**
     * Add group to an organization
     *
     * @param array $input
     * @param int $organization_id
     *
     * @return array
     */
    public function create($organization_id, array $input);

    /**
     * Update group
     *
     * @param array $input
     * @param int $organization_id
     * @param int $id
     *
     * @return array
     */
    public function update($organization_id, array $input, $id);

    /**
     * Get specific group
     *
     * @param int $organization_id
     * @param int $id
     * @return array
     */
    public function find($organization_id, $id);

    /**
     * Delete organization group
     *
     * @param int $organization_id
     * @param int $group_id
     *
     * @return array
     */
    public function delete($organization_id, $group_id);


    /**
     * Find a group using the source and source_id (e.g. an LDAP dn)
     *
     * @param string $source
     * @param string $source_id
     *
     * @return object
     */
    public function findBySource($organization_id, $source, $source_id);
}
