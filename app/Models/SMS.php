<?php

namespace RollCall\Models;

use Illuminate\Database\Eloquent\Model;

class SMS extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'outgoing_sms_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['to', 'from', 'driver', 'rollcall_id', 'type', 'message'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['id'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
    ];

}
