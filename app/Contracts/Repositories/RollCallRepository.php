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
     * Get counts for rollcall
     *
     * @param int $rollCallId
     * @return array
     */
    public function getCounts($rollCallId);
}
