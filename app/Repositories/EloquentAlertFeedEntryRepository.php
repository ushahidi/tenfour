<?php
namespace TenFour\Repositories;

use TenFour\Models\AlertFeedEntry;

use TenFour\Contracts\Repositories\AlertFeedEntryRepository;
use DB;

class EloquentAlertFeedEntryRepository implements AlertFeedEntryRepository
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
    public function all($feed_id = null, $owner_id = null, $source_type = null, $enabled = null, $offset = 0, $limit = 0) {
        
        $alerts = AlertFeedEntry::where('feed_id' ,'=', $feed_id)->with('feed')->get();
        return $alerts->toArray();
    }

    public function create(array $input)
    {
        $alert = AlertFeedEntry::create($input);

        return $alert->fresh()
            ->toArray();
    } 
    public function update(array $input, $id)
    {
        $alert = AlertFeedEntry::update($input);

        return $alert->fresh()
            ->toArray();
    }
    public function find($id)
    {
        $alert = AlertFeedEntry::find($id);

        return $alert->fresh()
            ->toArray();
    }

    public function delete($id)
    {
        $alert = AlertFeedEntry::delete($id);
        return $alert;
    }
}
