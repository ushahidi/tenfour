<?php
namespace RollCall\Contracts\Repositories;

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
    public function create($reply);

    /**
     * Update reply
     *
     * @param array $input
     * @param int $id

     * @return array
     */
    public function update(array $input, $id);

}
