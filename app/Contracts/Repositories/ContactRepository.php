<?php
namespace RollCall\Contracts\Repositories;

interface ContactRepository extends CrudRepository
{
    /**
     * Get the user who owns the contact
     *
     * @param int $contact_id
     *
     * return array
     */
    public function getUser($contact_id);

}
