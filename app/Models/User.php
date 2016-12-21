<?php

namespace RollCall\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use RollCall\Notifications\ResetPassword;

class User extends Model implements AuthenticatableContract,
	AuthorizableContract,
	CanResetPasswordContract
{
	use Authenticatable, Authorizable, CanResetPassword, Notifiable;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'description', 'email', 'password', 'invite_sent', 'invite_token', 'config_profile_reviewed', 'config_self_test_sent'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token', 'pivot', 'invite_token'];

	/**
	 * @param string $value
	 */
	public function setPasswordAttribute($value)
	{
		$this->attributes['password'] = Hash::make($value);
	}

	/**
	 * A user can have many roles
	 *
	 */
	public function roles()
	{
		return $this->belongsToMany('RollCall\Models\Role');
	}

	/**
	 * A user belongs to an organization
	 *
	 */
	public function organizations()
	{
		return $this->belongsToMany('RollCall\Models\Organization')->withPivot('role');
	}

	/**
     * A user has contacts
     *
     */
    public function contacts()
    {
        return $this->hasMany('RollCall\Models\Contact');
    }

    /**
     * An user has rollcalls
     *
     */
    public function rollcalls()
    {
        return $this->hasMany('RollCall\Models\RollCall');
    }

    /**
     *
     * Rollcalls that belong to the contact
     */
    public function receivedRollcalls()
    {
        return $this->belongsToMany('RollCall\Models\RollCall', 'roll_call_recipients');
    }

    public function replies()
    {
        return $this->hasMany('Rollcall\Models\Reply');
    }

		public function sendPasswordResetNotification($token) {
		    $this->notify(new ResetPassword($token, $this->organizations[0]));
		}
}
