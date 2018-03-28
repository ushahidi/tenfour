<?php
namespace TenFour\Contracts\Repositories;

interface ReplyRepository extends CrudRepository
{
    /**
     * Get specific reply
     *
     * @param int $id
     * @param int $reply_id
     * @return array
     */
    public function find($id);

    /**
     * Create reply
     *
     * @param array $reply
     * @return array
     */
    public function create(array $input);

    /**
     * Add reply
     *
     * @param array $reply
     * @return array
     */
    public function addReply(array $input, $id);

    /**
     * Get check-in replies
     *
     * @param int $id
     * @parom int $reply_id
     * @return array
     */
    public function getReplies($id, $users = null, $contacts = null);

    /**
     * Update reply
     *
     * @param array $input
     * @param int $id
     * @return array
     */
    public function update(array $input, $id);

    /**
     * Get last reply id from the provider
     *
     * @return int
     */
    public function getLastReplyId();

    /**
     * Save a reply
     */
    public function save($from, $message, $message_id = 0, $check_in_id = null, $provider = null, $outgoing_number = null);
}
