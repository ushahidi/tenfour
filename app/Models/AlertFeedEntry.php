<?php

namespace TenFour\Models;

use Illuminate\Database\Eloquent\Model;

class AlertFeedEntry extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'alert_feed_entry';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'feed_id',
        'title',
        'body',
        'metadata'
    ];

    /**
     *
     * Emergency Alert configuration belong to a user
     */
    public function feed()
    {
        return $this->belongsTo('TenFour\Models\AlertFeed');
    }

}
