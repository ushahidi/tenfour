<?php

namespace RollCall\Models;

use Illuminate\Database\Eloquent\Model;

class CreditAdjustment extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'credit_adjustments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['adjustment', 'balance', 'type'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
    ];

    /**
     * An organization has users
     *
     */
    public function organization()
    {
        return $this->belongsTo('RollCall\Models\Organization');
    }

}
