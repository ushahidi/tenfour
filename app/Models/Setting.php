<?php

namespace RollCall\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['organization_id', 'key', 'values'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['id', 'organization_id'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'values' => 'json'
    ];

    /**
     *
     * Roll calls belong to an organization
     */
    public function organization()
    {
        return $this->belongsTo('RollCall\Models\Organization');
    }

}
