<?php

namespace TenFour\Models;

use Illuminate\Database\Eloquent\Model;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Intervention\Image\Point;
use Illuminate\Support\Facades\Log;

class AlertSource extends Model
{
    protected $primaryKey = 'source_id';
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
     public $incrementing = false;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'alert_source';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'country', 'state', 'name', 'source_id', 'protocol', 'url', 'authentication_options', 'enabled'
    ];

    /**
     * optionally belong to an organization, if it doesn't, it's global
     */
    public function organization()
    {
        return $this->belongsTo('TenFour\Models\Organization');
    }
}
