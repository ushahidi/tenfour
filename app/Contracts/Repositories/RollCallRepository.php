<?php
namespace RollCall\Contracts\Repositories;

interface RollCallRepository extends CrudRepository
{
    /**
     * Add contacts to a roll call
     *
     * @param array $input
     * @param int $id

     * @return array
     */
    public function addContacts(array $input, $id);

    /**
     * Add contact to a roll call
     *
     * @param array $input
     * @param int $id

     * @return array
     */
    public function addContact(array $input, $id);

    /**
     * Get roll call contacts
     *
     * @param int $id

     * @return array
     */
    public function getContacts($id);

    /**
     * Get roll call replies
     *
     * @param int $id
     * @parom int $reply_id
     * @return array
     */
    public function getReplies($id);

    /**
     * Add roll call reply
     *
     * @param array $input
     * @param int $id

     * @return array
     */
    public function addReply(array $input, $id);
}
