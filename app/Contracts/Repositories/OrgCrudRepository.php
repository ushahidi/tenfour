<?php
namespace TenFour\Contracts\Repositories;

interface OrgCrudRepository
{
    /**
     * Get all
     *
     * @param int $organization_id
     * @param int $offset
     * @param int $limit
     * @return mixed
     */
    public function all($organization_id, $offset, $limit);

    /**
     * Create
     *
     * @param array $input
     * @param int $organization_id
     *
     * @return mixed
     */
    public function create($organization_id, array $input);

    /**
     * Update
     *
     * @param array $input
     * @param int $id
     * @param int $organization_id
     *
     * @return mixed
     */
    public function update($organization_id, array $input, $id);

    /**
     * Delete
     *
     * @param int $id
     * @param int $organization_id
     *
     * @return mixed
     */
    public function delete($organization_id, $id);

    /**
     * Find
     *
     * @param int $id
     * @param int $organization_id
     *
     * @return mixed
     */
    public function find($organization_id, $id);
}
