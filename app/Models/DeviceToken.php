<?php

namespace TenFour\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'device_tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['token'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['id', 'user_id'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
    ];

    /**
     *
     * Check-ins belong to an organization
     */
    public function user()
    {
        return $this->belongsTo('TenFour\Models\User');
    }
}
