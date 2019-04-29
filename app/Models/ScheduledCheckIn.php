<?php

namespace TenFour\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduledCheckin extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'scheduled_checkin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['starts_at', 'expires_at', 'remaining_count', 'check_ins', 'frequency'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [];
    /*
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
            ->get();
        return $scheduledCheckIns;
    }
}
