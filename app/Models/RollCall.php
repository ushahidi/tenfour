<?php

namespace RollCall\Models;

use Illuminate\Database\Eloquent\Model;

class RollCall extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'roll_calls';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['message', 'organization_id'];

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
     * Rollcalls belong to contact
     */
    public function contacts()
    {
        return $this->belongsToMany('RollCall\Models\Contact');
    }
}
