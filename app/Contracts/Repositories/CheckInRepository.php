<?php
namespace TenFour\Contracts\Repositories;

interface CheckInRepository extends CrudRepository
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
     * Get check-in recipients
     *
     * @param int $id

     * @return array
     */
    public function getRecipients($id, $unresponsive=null);

    /**
     * Get check-in sent messages and optionally filter by user
     *
     * @param int $id

     * @return array
     */
    public function getMessages($id, $user_id = null);

    /**
     * Get counts for check-in
     *
     * @param int $check_in_id
     * @return array
     */
    public function getCounts($check_in_id);

    /**
     * Get last sent message id and optionally filter by contact
     *
     * @return int
     */
    public function getLastSentMessageId($contact_id = null);

    /**
     * Check check-in was sent to contact id
     *
     * @param  $contact_id
     * @param  $check_in_id
     * @return $check_in_id
     */
    public function getSentCheckInId($contact_id, $check_in_id);

    /**
     * Add check-in sent to contact
     *
     * @param int $id
     * @param int $contact_id

     * @return array
     */
    public function addMessage($id, $contact_id, $from);

    /**
     * Update a user's response status
     *
     * @param int $check_in_id
     * @param int $user_id
     * @param int $status
     */
    public function updateRecipientStatus($check_in_id, $user_id, $status);

    /**
     * Set a reply token for a user's response
     *
     * @param int $check_in_id
     * @param int $user_id
     */
    public function setReplyToken($check_in_id, $user_id);

    /**
     * Get pending check-in reply by contact
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
     * Get pending check-in reply by contact
     *
     * @param int $contact_id
     *
     * @return int
     */
    public function getLastUnrepliedByUser($user_id);

    /**
     * Does this $from number have any unreplied check-ins for a contact
     *
     * @param $from
     * @param int $contact_id
     */
    public function isOutgoingNumberActive($contact_id, $from);

    /**
     * Get the outgoing number that a check-in was sent to a Contact
     *
     * @param int $check_in_id
     * @param int $contact_id
     */
    public function getOutgoingNumberForCheckInToContact($check_in_id, $contact_id);

    /**
     * Has a user replied to a check-in
     *
     * @param int $user_id
     * @param int $check_in_id
     */
    public function hasRepliedToCheckIn($user_id, $check_in_id);
}
