<?php
namespace RollCall\Contracts\Repositories;

interface RollCallRepository extends CrudRepository
{
    /**
     * Get all
     * @param  [int] $org_id
     * @param  [int] $user_id
     * @param  [int] $recipient_id
     * @param  [int] $offset
     * @param  [int] $limit
     * @return [Array]
     */
    public function all($org_id = null, $user_id = null, $recipient_id = null, $auth_user_id = null, $offset = 0, $limit = 0);

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
     * Check rollcall was setn to contact id
     *
     * @param  $contact_id
     * @param  $roll_call_id
     * @return $roll_call_id
     */
    public function getSentRollCallId($contact_id, $roll_call_id);

    /**
     * Add roll call sent to contact
     *
     * @param int $id
     * @param int $contact_id

     * @return array
     */
    public function addMessage($id, $contact_id, $from);

    /**
     * Update a user's response status
     *
     * @param int $roll_call_id
     * @param int $user_id
     * @param int $status
     */
    public function updateRecipientStatus($roll_call_id, $user_id, $status);

    /**
     * Set a reply token for a user's response
     *
     * @param int $roll_call_id
     * @param int $user_id
     */
    public function setReplyToken($roll_call_id, $user_id);

    /**
     * Get pending roll call reply by contact
     *
     * @param int $contact_id
     *
     * @return int
     */
    public function getLastUnrepliedByContact($contact_id, $from);

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

    /**
     * Does this $from number have any unreplied rollcalls for a contact
     *
     * @param $from
     * @param int $contact_id
     */
    public function isOutgoingNumberActive($contact_id, $from);

    /**
     * Get the outgoing number that a RollCall was sent to a Contact
     *
     * @param int $roll_call_id
     * @param int $contact_id
     */
    public function getOutgoingNumberForRollCallToContact($roll_call_id, $contact_id);

    /**
     * Has a user replied to a rollcall
     *
     * @param int $user_id
     * @param int $roll_call_id
     */
    public function hasRepliedToRollCall($user_id, $roll_call_id);
}
