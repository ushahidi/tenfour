<?php
namespace TenFour\Contracts\Repositories;

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
     * Filter contacts by contact (email address, phone number)
     *
     * @param string $contact
     * @param int $org_id
     *
     * @return Array
     */
    public function getByContact($contact, $org_id = null);

    /**
     * Get the most recently used matching contact
     *
     * @param string $contact
     *
     * @return Array
     */
    public function getByMostRecentlyUsedContact($contact);

    /**
     * Set bounce count
     *
     * @param int $count
     * @param int $id
     *
     */
    public function setBounceCount($count, $id);
}
