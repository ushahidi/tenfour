<?php
namespace TenFour\Contracts\Repositories;

interface ScheduledCheckInRepository extends CrudRepository
{
    /**
     * Get all
     *
     * @param [type] $org_id
     * @param [type] $user_id
     * @param integer $offset
     * @param integer $limit
     * @return Array
     */
    public function all($org_id = null, $user_id = null, $offset = 0, $limit = 0);

    /**
     * Delete
     *
     * @param int $id
     *
     * @return mixed
     */
    public function delete($id);

}
