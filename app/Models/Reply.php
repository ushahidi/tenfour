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
    protected $fillable = ['message', 'roll_call_id', 'contact_id'];

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
}
