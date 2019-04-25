<?php
namespace TenFour\Repositories;

use TenFour\Models\CheckIn;
use TenFour\Models\Reply;
use TenFour\Contracts\Repositories\ScheduledCheckInRepository;
use DB;
use Illuminate\Support\Facades\Hash;
use TenFour\Models\ScheduledCheckIn;

class EloquentScheduledCheckInRepository implements ScheduledCheckInRepository
{
    public function __construct()
    {
    }

    public function all($org_id = null, $user_id = null, $offset = 0, $limit = 0)
    {
        $query = ScheduledCheckIn::query()
          ->where('check_ins.organization_id', '=', $org_id)
          ->where('check_ins.user_id', '=', $user_id);
        $query->join('check_ins', 'check_ins.scheduled_check_in_id', 'scheduled_check_in.id');
        $query->orderBy('scheduled_check_in.created_at', 'desc');
        if ($limit > 0) {
            $query
            ->offset($offset)
            ->limit($limit);
        }
        $scheduled_check_ins = $query->get()->toArray();
        return $scheduled_check_ins;
    }

    public function pending($org_id = null, $user_id = null, $offset = 0, $limit = 0) 
    {
        $query = ScheduledCheckIn::query()
          ->where('check_ins.template', '=', 0)
          ->where('check_ins.sent', '=', 0)
          ->where('check_ins.organization_id', '=', $org_id);
          if ($user_id) {
            $query->where('check_ins.user_id', '=', $user_id);
          }
          
          $query->join('check_ins', 'check_ins.scheduled_check_in_id', 'scheduled_check_in.id');
        
          $query->orderBy('scheduled_check_in.created_at', 'desc');
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
        return ScheduledCheckIn::destroy($id);
    }

    /**
     * Find
     *
     * @param int $id
     *
     * @return mixed
     */
    public function find($id) {
        $query = ScheduledCheckIn::query()
          ->with(['check_ins' => function ($query) use ($id){
            $query->where('check_ins.scheduled_check_in_id', $id);
          }]);
        return $query->findOrFail($id);
    }
}
