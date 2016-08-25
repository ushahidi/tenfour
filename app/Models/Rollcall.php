<?php

namespace RollCall\Models;

use Illuminate\Database\Eloquent\Model;

class Rollcall extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rollcalls';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['message', 'contact_id', 'organization_id', 'user_id'];

    /**
     *
     * Rollcalls belong to an organization
     */
    public function organization()
    {
        return $this->belongsTo('RollCall\Models\Organization');
    }

    /**
     *
     * Rollcalls belong to a user
     */
    public function user()
    {
        return $this->belongsTo('RollCall\Models\User');
    }
}
