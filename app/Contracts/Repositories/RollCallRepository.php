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
     * Get roll call sent messages
     *
     * @param int $id

     * @return array
     */
    public function getMessages($id);

    /**
     * Get roll call replies
     *
     * @param int $id
     * @parom int $reply_id
     * @return array
     */
    public function getReplies($id, $users = null, $contacts = null);

    /**
     * Add roll call reply
     *
     * @param array $input
     * @param int $id

     * @return array
     */
    public function addReply(array $input, $id);

    /**
     * Get counts for rollcall
     *
     * @param int $rollCallId
     * @return array
     */
    public function getCounts($rollCallId);
}
