<?php
namespace TenFour\Models;

use Illuminate\Database\Eloquent\Model;
use Tenfour\Models\AlertFeedEntry;
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
/**
     * The relations to eager load on every query.
     *
     * @var array
     */
    // protected $with = ['entries'];

    protected $fillable = [
        'automatic',
        'country',
        'state',
        'source_id',
        'enabled',
        'organization_id',
        'owner_id'
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

    // public function entries() {
    // return $this->hasMany('Tenfour\Models\AlertFeedEntry');
    // }
}
