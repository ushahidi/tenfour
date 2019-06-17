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
        throw new InterfaceNotImplementedException('Not yet implemented', 'AlertFeed', TenFour\Contracts\Repositories\AlertFeedRepository);
    }

    /**
     * mysql> desc alert_feed;
     * +-----------------+---------------------+------+-----+---------+----------------+
     * | Field           | Type                | Null | Key | Default | Extra          |
     * +-----------------+---------------------+------+-----+---------+----------------+
     * | id              | bigint(20) unsigned | NO   | PRI | NULL    | auto_increment |
     * | owner_id        | int(10) unsigned    | NO   | MUL | 0       |                |
     * | organization_id | int(10) unsigned    | NO   | MUL | 0       |                |
     * | country         | varchar(255)        | NO   |     | NULL    |                |
     * | city            | varchar(255)        | NO   |     | NULL    |                |
     * | source_id       | varchar(255)        | NO   | MUL | NULL    |                |
     * | enabled         | text                | NO   |     | NULL    |                |
     * | created_at      | timestamp           | YES  |     | NULL    |                |
     * | updated_at      | timestamp           | YES  |     | NULL    |                |
     * +-----------------+---------------------+------+-----+---------+----------------+
     *
     * @param array $input
     * @return void
     */ 
    public function create(array $input)
    {
        $alert = AlertFeed::create($input);

        return $alert->fresh()
            ->toArray();
    } 
    public function update(array $input, $id)
    {
        $alert = AlertFeed::update($input, $id);

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
