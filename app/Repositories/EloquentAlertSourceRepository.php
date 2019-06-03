<?php
namespace TenFour\Repositories;

use TenFour\Models\AlertFeed;
use TenFour\Models\AlertSource;
use TenFour\Models\AlertSubscription;
use TenFour\Contracts\Repositories\AlertSourceRepository;
use DB;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use TenFour\Notifications\CheckInReceived;
use Illuminate\Support\Facades\Log;
use PhpSpec\Exception\Fracture\InterfaceNotImplementedException;

class EloquentAlertSourceRepository implements AlertSourceRepository
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
            $alerts = AlertSource::where('enabled' ,'=', intval($enabled))->get();
        } else {
            $alerts = AlertSource::all();
        }
        return $alerts->toArray();
    }

    public function create(array $input)
    {
        $alert = AlertSource::create($input);

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
     public function update(array $input, $feed_id) {

        $alert = AlertSource::update(['feed_id' => $feed_id], $input);

        return $alert->toArray();
     }

     /**
      * Delete
      *
      * @param int $id
      *
      * @return mixed
      */
     public function delete($feed_id) {

        $alert = AlertSource::find($feed_id)->delete();

        return $alert;
     }
 
     /**
      * Find
      *
      * @param int $feed_id
      *
      * @return mixed
      */
     public function find($feed_id) {

        $alert = AlertSource::get(['feed_id' => $feed_id]);

        return $alert;
     }
}
