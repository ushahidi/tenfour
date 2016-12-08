<?php
namespace RollCall\Contracts\Repositories;

interface RollCallRepository extends CrudRepository
{
    /**
     * Get roll call recipients
     *
     * @param int $id

     * @return array
     */
    public function getRecipients($id, $unresponsive=null);

    /**
     * Get roll call sent messages and optionally filter by user
     *
     * @param int $id

     * @return array
     */
    public function getMessages($id, $user_id = null);

    /**
     * Get counts for rollcall
     *
     * @param int $rollCallId
     * @return array
     */
    public function getCounts($rollCallId);

    /**
     * Get last sent message id and optionally filter by contact
     *
     * @return int
     */
    public function getLastSentMessageId($contact_id = null);

    /**
     * Add roll call sent to contact
     *
     * @param int $id
     * @param int $contact_id

     * @return array
     */
    public function addMessage($id, $contact_id);

}
