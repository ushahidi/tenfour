<?php
namespace RollCall\Contracts\Repositories;

interface UserRepository extends CrudRepository
{
     /**
     * Get a user's roles
     *
     * @param int $id
     *
     * @return array
     */
    public function getRoles($id);
}
