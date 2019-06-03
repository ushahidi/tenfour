<?php

namespace TenFour\Models;

use Illuminate\Database\Eloquent\Model;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Intervention\Image\Point;
use Illuminate\Support\Facades\Log;

class AlertFeed extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'alert_feed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'country', 'city', 'source_type', 'source_config', 'enabled', 'organization_id', 'owner_id'
    ];

    /**
     *
     * Emergency Alert configuration belong to a user
     */
    public function user()
    {
        return $this->belongsTo('TenFour\Models\User');
    }

    /**
     *
     * belong to an organization
     */
    public function organization()
    {
        return $this->belongsTo('TenFour\Models\Organization');
    }
}
