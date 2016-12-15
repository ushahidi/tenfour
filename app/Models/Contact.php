<?php

namespace RollCall\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
	protected $table = 'contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type', 'can_receive', 'user_id', 'contact'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['pivot'];

    /**
     * Messages sent to contact
     */
    public function rollcalls()
    {
        return $this->belongsToMany('RollCall\Models\RollCall', 'roll_call_messages');
    }

    /**
     * Get the user that owns the contact.
     */
    public function user()
    {
        return $this->belongsTo('RollCall\Models\User');
    }


    public function replies()
    {
        return $this->hasMany('RollCall\Models\Reply');
    }
}
