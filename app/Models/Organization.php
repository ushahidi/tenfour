<?php

namespace RollCall\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'organizations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'url'];

    /**
     * An organization has users
     *
     */
    public function users()
    {
        return $this->belongsToMany('RollCall\Models\User')->withPivot('role');
    }

    /**
     *
     * An organization has rollcalls
     */
    public function rollcalls()
    {
        return $this->hasMany('RollCall\Models\Rollcall');
    }
}
