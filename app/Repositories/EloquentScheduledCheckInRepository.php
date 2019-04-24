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
          ->with(['check_ins' => function ($query) use ($org_id, $user_id){
            if ($org_id) {
                $query->where('check_ins.organization_id', $org_id);
            }
    
            if ($user_id) {
                $query->where('user_id', $user_id);
            }
          }]);
        $query->orderBy('created_at', 'desc');
        if ($limit > 0) {
            $query
            ->offset($offset)
            ->limit($limit);
        }

        $scheduled_check_ins = $query->get()->toArray();
        return $scheduled_check_ins;
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
