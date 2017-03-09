<?php
namespace RollCall\Contracts\Repositories;

interface CrudRepository
{
    /**
     * Get all
     *
     * @return mixed
     */
    public function all();

    /**
     * Create
     *
     * @param array $input
     *
     * @return mixed
     */
    public function create(array $input);

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
