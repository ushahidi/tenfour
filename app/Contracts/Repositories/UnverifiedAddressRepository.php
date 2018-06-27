<?php
namespace TenFour\Contracts\Repositories;

interface UnverifiedAddressRepository extends CrudRepository
{
    /**
     * Filter by address and optionally by token
     *
     * @param string $address
     * @param string $token if verifiying with token
     * @param string $code if verifying with code
     *
     * @return Array
     */
    public function getByAddress($address, $token = false, $code = false);

    /**
     * Increment the code attempts counter for this address
     *
     * @param string $address
     *
     */
    public function incrementCodeAttempts($address);
}
