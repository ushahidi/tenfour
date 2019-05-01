<?php
namespace TenFour\Repositories;

use TenFour\Models\CheckIn;
use TenFour\Models\Reply;
use TenFour\Contracts\Repositories\ScheduledCheckinRepository;
use DB;
use Illuminate\Support\Facades\Hash;
use TenFour\Models\ScheduledCheckin;

class EloquentScheduledCheckinRepository implements ScheduledCheckinRepository
{
    public function __construct()
    {
    }

    public function all($org_id = null, $user_id = null, $offset = 0, $limit = 0)
    {
        $query = ScheduledCheckin::query()
          ->where('check_ins.organization_id', '=', $org_id)
          ->where('check_ins.user_id', '=', $user_id);
        $query->join('check_ins', 'check_ins.scheduled_checkin_id', 'scheduled_checkin.id');
        $query->orderBy('scheduled_checkin.created_at', 'desc');
        if ($limit > 0) {
            $query
            ->offset($offset)
            ->limit($limit);
        }
        $scheduled_checkins = $query->get()->toArray();
        return $scheduled_checkins;
    }

    public function pending($org_id = null, $user_id = null, $offset = 0, $limit = 0) 
    {
        $query = CheckIn::query()
        ->with('scheduledCheckin')
          ->where('template', '=', 0)
          ->whereRaw('scheduled_checkin_id IS NOT NULL')
          ->where('sent', '=', 0)
          ->where('organization_id', '=', $org_id);
          if ($user_id) {
            $query->where('user_id', '=', $user_id);
          }
          $query->orderBy('created_at', 'desc');
          if ($limit > 0) {
              $query
              ->offset($offset)
              ->limit($limit);
          }

          return $query->get()->toArray();
    }
    /**
     * Create
     *
     * @param array $input
     *
     * @return mixed
     */
    public function create(array $input) {

    }

    /**
     * Update
     *
     * @param array $input
	 * @param int $id
     *
     * @return mixed
     */
    public function update(array $input, $id) {

    }

    /**
     * Delete
     *
     * @param int $id
     *
     * @return mixed
     */
    public function delete($id) {
        return ScheduledCheckin::destroy($id);
    }

    /**
     * Find
     *
     * @param int $id
     *
     * @return mixed
     */
    public function find($id) {
        $query = ScheduledCheckin::query()
          ->with(['check_ins' => function ($query) use ($id){
            $query->where('check_ins.scheduled_checkin_id', $id);
          }]);
        return $query->findOrFail($id);
    }
}
