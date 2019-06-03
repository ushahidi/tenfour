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

class EloquenAlertFeedRepository implements AlertFeedRepository
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
        throw new InterfaceNotImplementedException('Not yet implemented', 'AlertFeed', TenFour\Contracts\Repositories\AlertFeedRepository);
    }


    public function create(array $input)
    {
        $alert = AlertFeed::create($input);

        return $alert->fresh()
            ->toArray();
    }
}
