<?php
namespace TenFour\Contracts\Repositories;

interface AlertFeedRepository extends CrudRepository
{
    /**
     * Get all
     * @param  [int] $org_id
     * @param  [int] $owner_id
     * @param  [int] $source_type
     * @param  [int] $offset
     * @param  [int] $limit
     * @return [Array]
     */
    public function all($org_id = null, $owner_id = null, $source_type = null, $enabled = null, $offset = 0, $limit = 0);

}
