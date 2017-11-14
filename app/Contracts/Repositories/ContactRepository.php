<?php
namespace RollCall\Contracts\Repositories;

interface ContactRepository extends CrudRepository
{
    /**
     * Filter contacts by user id
     *
     * @param int $user_id
     *
     * @return Array
     */
    public function getByUserId($user_id, Array $methods = null);

    /**
     * Filter contacts by contact
     *
     * @param string $contact
     *
     * @return Array
     */
    public function getByContact($contact, $org_id = null);

    /**
     * Set bounce count
     *
     * @param int $count
     * @param int $id
     *
     */
    public function setBounceCount($count, $id);
}
