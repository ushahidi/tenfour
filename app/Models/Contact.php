<?php

namespace TenFour\Models;

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
    protected $fillable = ['type', 'preferred', 'user_id', 'organization_id', 'contact', 'passed_self_test', 'unsubscribe_token', 'blocked', 'meta'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'meta' => 'json'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['pivot'];

    /**
     * Messages sent to contact
     */
    public function checkIns()
    {
        return $this->belongsToMany('TenFour\Models\CheckIn', 'check_in_messages');
    }

    /**
     * Get the user that owns the contact.
     */
    public function user()
    {
        return $this->belongsTo('TenFour\Models\User');
    }


    public function replies()
    {
        return $this->hasMany('TenFour\Models\Reply');
    }
}
