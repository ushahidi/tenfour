<?php

namespace TenFour\Models;

use Illuminate\Database\Eloquent\Model;

class Mail extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'outgoing_mail_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['to', 'from', 'subject', 'check_in_id', 'type'];

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
