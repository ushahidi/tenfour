<?php
namespace TenFour\Repositories;

use TenFour\Models\AlertSubscription;
use TenFour\Contracts\Repositories\AlertSubscriptionRepository;
use DB;

class EloquentAlertSubscriptionRepository implements AlertSubscriptionRepository
{

    public function __construct()
    {
    }

    /**
     * Get all
     * @param  [int] $organization_id
     * @param  [int] $enabled
     * @param  [int] $offset
     * @param  [int] $limit
     * @return [Array]
     */
    public function all($organization_id = null, $enabled = null, $offset = 0, $limit = 0) {
        
        if (is_bool($enabled)) {
            $alerts = AlertSubscription::where('enabled' ,'=', intval($enabled))->get();
        } else {
            $alerts = AlertSubscription::all();
        }
        return $alerts->toArray();
    }

    public function create(array $input)
    {
        $alert = AlertSubscription::create($input);

        return $alert->fresh();
    }

    /**
     * Update
     *
     * @param array $input
	 * @param int $id
     *
     * @return mixed
     */
     public function update(array $input, $source_id) {

        $alert = AlertSubscription::update(['source_id' => $source_id], $input);

        return $alert->toArray();
     }

     /**
      * Delete
      *
      * @param int $id
      *
      * @return mixed
      */
     public function delete($source_id) {

        $alert = AlertSubscription::find($source_id)->delete();

        return $alert;
     }
 
     /**
      * Find
      *
      * @param int $source_id
      *
      * @return mixed
      */
     public function find($source_id) {

        $alert = AlertSubscription::get(['source_id' => $source_id]);

        return $alert;
     }
}
