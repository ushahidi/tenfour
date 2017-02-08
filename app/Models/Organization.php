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
    protected $fillable = ['name', 'subdomain'];

    /**
     * An organization has users
     *
     */
    public function members()
    {
        return $this->hasMany('RollCall\Models\User');
    }

    /**
     *
     * An organization has rollcalls
     */
    public function rollcalls()
    {
        return $this->hasMany('RollCall\Models\RollCall');
    }

    /**
     *
     * An organization has settings
     */
    public function settings()
    {
        return $this->hasMany('RollCall\Models\Setting');
    }

    public function url()
    {
        return 'https://' .
            $this->subdomain .
            '.' .
            config('rollcall.domain');
    }

}
