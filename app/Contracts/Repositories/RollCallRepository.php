<?php
namespace RollCall\Contracts\Repositories;

interface RollCallRepository extends CrudRepository
{
    /**
     * Get all
     * @param  [int] $org_id
     * @param  [int] $user_id
     * @param  [int] $recipient_id
     * @return [Array]
     */
    public function all($org_id = null, $user_id = null, $recipient_id = null);

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

    /**
     * Update a user's response status
     *
     * @param int $roll_call_id
     * @param int $user_id
     * @param int $status
     */
    public function updateRecipientStatus($roll_call_id, $user_id, $status);

    /**
     * Get pending roll call reply by contact
     *
     * @param int $contact_id
     *
     * @return int
     */
    public function getLastUnrepliedByContact($contact_id);

    /**
     * Set complaint count
     *
     * @param int $count
     * @param int $contact_id
     *
     * @return array
     */
    public function setComplaintCount($count, $id);

    /**
     * Get complaint count by organization
     *
     * @param int $org_id
     *
     * @return int
     */
    public function getComplaintCountByOrg($org_id);

    /**
     * Get pending roll call reply by contact
     *
     * @param int $contact_id
     *
     * @return int
     */
    public function getLastUnrepliedByUser($user_id);
}
