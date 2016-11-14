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

class User extends Model implements AuthenticatableContract,
	AuthorizableContract,
	CanResetPasswordContract
{
	use Authenticatable, Authorizable, CanResetPassword;

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
	protected $fillable = ['name', 'username', 'email', 'password'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

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
}
