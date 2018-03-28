<?php

namespace TenFour\Models;

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
    protected $fillable = ['name', 'subdomain', 'profile_picture', 'features'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'features' => 'json'
    ];

    /**
     * An organization has users
     *
     */
    public function members()
    {
        return $this->hasMany('TenFour\Models\User');
    }

    public function subscriptions()
    {
        return $this->hasMany('TenFour\Models\Subscription');
    }

    public function currentSubscription()
    {
        if (count($this->subscriptions)) {
            return $this->subscriptions[0];
        } else {
            return null;
        }
    }

    public function owner()
    {
        return $this->members->where('role', 'owner')->first();
    }

    /**
     *
     * An organization has check-ins
     */
    public function checkIns()
    {
        return $this->hasMany('TenFour\Models\CheckIn');
    }

    /**
     *
     * An organization has settings
     */
    public function settings()
    {
        return $this->hasMany('TenFour\Models\Setting');
    }

    public function url($path = '')
    {
        if (config('app.env') === 'local') {
            return 'http://localhost:8080' . $path;
        }

        return 'https://' .
            $this->subdomain .
            '.' .
            config('tenfour.domain') .
            $path;
    }

    /*
     *
     * An organization has groups
     */
    public function groups()
    {
        return $this->hasMany('TenFour\Models\Group');
    }

}
