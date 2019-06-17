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
     public function update(array $input, $source_id) {

        $alert = AlertSource::update(['source_id' => $source_id], $input);

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

        $alert = AlertSource::find($source_id)->delete();

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

        $alert = AlertSource::get(['source_id' => $source_id]);

        return $alert;
     }
}
