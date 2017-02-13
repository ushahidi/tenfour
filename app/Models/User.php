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
use Illuminate\Support\Str;

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

    protected $fillable = ['name', 'description', 'password', 'invite_sent', 'invite_token', 'config_profile_reviewed', 'config_self_test_sent', 'person_type', 'role'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token', 'invite_token'];

    /**
     * @param string $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * A user belongs to an organization
     *
     */
    public function organization()
    {
        return $this->belongsTo('RollCall\Models\Organization');
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
        $this->notify(new ResetPassword($token, $this->organization));
    }

    public function getEmailForPasswordReset()
    {
        return $this->contact;
    }

    public function hasLoggedIn()
    {
            return isset($this->password) && !empty($this->password);
    }

    /**
     * Get the notification routing information for the given driver.
     *
     * @param  string  $driver
     * @return mixed
     */
    public function routeNotificationFor($driver)
    {
        if (method_exists($this, $method = 'routeNotificationFor'.Str::studly($driver))) {
                return $this->{$method}();
        }
        switch ($driver) {
            case 'database':
                return $this->notifications();
            case 'mail':
                return $this->contact;
            case 'nexmo':
                return $this->phone_number;
        }
    }

    public function isAdmin($orgId)
    {
        if ($this->organization_id != $orgId) {
            return false;
        }

        // @todo should this include owner too?
        //return $this->auth->user()['role'] === 'admin';
        return in_array($this->role, ['admin', 'owner']);
    }

    public function isMember($orgId)
    {
        if ($this->organization_id != $orgId) {
            return false;
        }

        return in_array($this->role, ['admin', 'member', 'owner']);
    }

    public function isOwner($orgId)
    {
        if ($this->organization_id != $orgId) {
            return false;
        }

        return $this->role === 'owner';
    }
}
