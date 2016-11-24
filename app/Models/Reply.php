<?php

namespace RollCall\Models;

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
    protected $fillable = ['message', 'roll_call_id', 'user_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['pivot'];

    /**
     *
     * Replies belong to a roll call
     */
    public function rollcall()
    {
        return $this->belongsTo('RollCall\Models\RollCall');
    }

    /**
     *
     * Replies belong to a contact
     */
    public function contact()
    {
        return $this->belongsTo('RollCall\Models\Contact');
    }

    /**
     *
     * Replies belong to a contact
     */
    public function user()
    {
        return $this->belongsTo('RollCall\Models\User');
    }
}
