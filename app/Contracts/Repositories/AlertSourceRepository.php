<?php
namespace TenFour\Contracts\Repositories;

interface AlertSourceRepository extends CrudRepository
{
    /**
     * Get all
     * @param  [int] $feed_id
     * @param  [int] $name
     * @param  [int] $enabled
     * @param  [int] $offset
     * @param  [int] $limit
     * @return [Array]
     */
    public function all($organization_id = null, $enabled = null, $offset = 0, $limit = 0);

    /**
     * Update
     *
     * @param array $input
	 * @param int $id
     *
     * @return mixed
     */
     public function update(array $input, $id);

     /**
      * Delete
      *
      * @param int $id
      *
      * @return mixed
      */
     public function delete($id);
 
     /**
      * Find
      *
      * @param int $id
      *
      * @return mixed
      */
     public function find($id);
}
