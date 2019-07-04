<?php

namespace TenFour\Models;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'replies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = ['answer', 'location_text', 'location_geo', 'message', 'contact_id', 'check_in_id', 'user_id', 'message_id', 'response_time'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['pivot'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'location_geo' => 'json'
    ];

    /**
     *
     * Replies belong to a check-in
     */
    public function checkin()
    {
        return $this->belongsTo('TenFour\Models\CheckIn');
    }

    /**
     *
     * Replies belong to a contact
     */
    public function contact()
    {
        return $this->belongsTo('TenFour\Models\Contact');
    }

    /**
     *
     * Replies belong to a contact
     */
    public function user()
    {
        return $this->belongsTo('TenFour\Models\User');
    }
}
