<?php

namespace TenFour\Models;

use Illuminate\Database\Eloquent\Model;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Intervention\Image\Point;
use Illuminate\Support\Facades\Log;

class AlertSubscription extends Model
{
    /**
     * The database table used by the model.
     * who will receive the alerts
     * @var string
     */
    protected $table = 'alert_subscription';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'group_id', 'user_id', 'automatic'
    ];

    /**
     * optionally belong to an organization, if it doesn't, it's global
     */
    public function organization()
    {
        return $this->belongsTo('TenFour\Models\Organization');
    }

    /**
     * optionally belong to an organization, if it doesn't, it's global
     */
    public function user()
    {
        return $this->belongsTo('TenFour\Models\User');
    }
}
