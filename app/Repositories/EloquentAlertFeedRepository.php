<?php
namespace TenFour\Repositories;

use TenFour\Models\AlertFeed;
use TenFour\Models\AlertSource;
use TenFour\Models\AlertSubscription;
use TenFour\Contracts\Repositories\AlertFeedRepository;
use DB;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use TenFour\Notifications\CheckInReceived;
use Illuminate\Support\Facades\Log;
use PhpSpec\Exception\Fracture\InterfaceNotImplementedException;

class EloquentAlertFeedRepository implements AlertFeedRepository
{
    public function __construct()
    {
    }

    /**
     * Get all
     * @param  [int] $org_id
     * @param  [int] $owner_id
     * @param  [int] $source_type
     * @param  [int] $offset
     * @param  [int] $limit
     * @return [Array]
     */
    public function all($org_id = null, $owner_id = null, $source_type = null, $enabled = null, $offset = 0, $limit = 0) {
        
        $alerts = AlertFeed::where('organization_id' ,'=', $org_id)->get();
        return $alerts->toArray();
    }

    public function create(array $input)
    {
        $alert = AlertFeed::create($input);

        return $alert->fresh()
            ->toArray();
    } 
    public function update(array $input, $id)
    {
        $alert = AlertFeed::update($input);

        return $alert->fresh()
            ->toArray();
    }
    public function find($id)
    {
        $alert = AlertFeed::find($id);

        return $alert->fresh()
            ->toArray();
    }

    public function delete($id)
    {
        $alert = AlertFeed::delete($id);
        return $alert;
    }
}
