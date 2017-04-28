<?php
namespace RollCall\Contracts\Repositories;

interface UnverifiedAddressRepository extends CrudRepository
{
    /**
     * Filter by address and optionally by token
     *
     * @param string $address
     * @param string $token
     *
     * @return Array
     */
    public function getByAddress($address, $token = false);
}
