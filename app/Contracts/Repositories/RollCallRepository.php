<?php
namespace RollCall\Contracts\Repositories;

interface RollCallRepository extends CrudRepository
{
    /**
     * Filter rollcalls by organization id
     *
     * @param int $org_id
     *
     * @return array
     */
    public function filterByOrganizationId($org_id);
}
