<?php

namespace TenFour\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduledCheckIn extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'scheduled_check_in';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['starts_at', 'expires_at', 'remaining_count', 'check_ins', 'frequency', 'check_ins_id'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['check_ins'];
    /**
     * Get the check_in record associated with the scheduler.
     */
    public function check_ins()
    {
        return $this->belongsTo('TenFour\Models\CheckIn');
    }
    /**
     * Get all of the scheduled check ins that are active, meaning they still
     * have check ins to be delivered and haven't not expired
     * @param  array|mixed  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findActive()
    {
         $scheduledCheckIns = self::where([
                ['scheduled', '=', 0]
            ])
            ->with('check_ins')
            ->get();
        return $scheduledCheckIns;
    }
}
