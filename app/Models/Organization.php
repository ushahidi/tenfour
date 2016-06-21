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
    protected $fillable = ['name', 'url', 'user_id'];

    /**
     * An organization has admins
     *
     */
    public function admins()
    {
        return $this->belongsToMany('RollCall\Models\User', 'organization_admins', 'organization_id', 'user_id');
    }
}
