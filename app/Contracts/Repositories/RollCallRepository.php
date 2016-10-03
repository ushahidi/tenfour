<?php
namespace RollCall\Contracts\Repositories;

interface RollCallRepository extends CrudRepository
{
    /**
     * Filter roll calls by organization id
     *
     * @param int $org_id
     *
     * @return array
     */
    public function filterByOrganizationId($org_id);

    /**
     * Add contacts to roll call
     *
     * @param array $input
     * @param int $id

     * @return array
     */
    public function addContacts(array $input, $id);

    /**
     * list roll call contacts
     *
     * @param int $id

     * @return array
     */
    public function listContacts($id);
}
