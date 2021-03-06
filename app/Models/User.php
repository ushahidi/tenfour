<?php

namespace TenFour\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use TenFour\Notifications\ResetPassword;
use Illuminate\Support\Str;
use TenFour\Models\Mail as OutgoingMail;
use Laravel\Passport\HasApiTokens;

class User extends Model implements AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, HasApiTokens;

    use Notifiable {
      notify as protected notifiableNotify;
    }

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
    protected $fillable = ['name', 'description', 'password', 'invite_sent', 'invite_token', 'config_profile_reviewed', 'config_self_test_sent', 'person_type', 'role', 'profile_picture', 'first_time_login', 'terms_of_service', 'source', 'source_id'];

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
        return $this->belongsTo('TenFour\Models\Organization');
    }

    /**
     * A user has contacts
     *
     */
    public function contacts()
    {
        return $this->hasMany('TenFour\Models\Contact');
    }

    /**
     * An user has check-ins
     *
     */
    public function checkIns()
    {
        return $this->hasMany('TenFour\Models\CheckIn');
    }

    /**
     *
     * CheckIns that belong to the contact
     */
    public function receivedCheckIns()
    {
        return $this->belongsToMany('TenFour\Models\CheckIn', 'check_in_recipients');
    }

    public function replies()
    {
        return $this->hasMany('TenFour\Models\Reply');
    }

    public function deviceTokens()
    {
        return $this->hasMany('TenFour\Models\DeviceToken');
    }


    public function sendPasswordResetNotification($token) {
        $this->notify(new ResetPassword($token, $this->getEmailForPasswordReset(), $this->organization));
    }

    public function getEmailForPasswordReset()
    {
        return $this->contact;
    }

    public function email()
    {
        $email = $this->contacts->where('type', 'email')->first();

        return $email ? $email->contact : null;
    }

    public function phone()
    {
        $phone = $this->contacts->where('type', 'phone')->first();

        return $phone ? $phone->contact : null;
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
                return $this->email();
            case 'nexmo':
                return $this->phone();
        }
    }

    public function isAdmin($orgId)
    {
        if ($this->organization_id != $orgId) {
            return false;
        }

        return in_array($this->role, ['admin', 'owner']);
    }

    public function isMember($orgId)
    {
        if ($this->organization_id != $orgId) {
            return false;
        }

        return in_array($this->role, ['admin', 'responder', 'owner', 'author', 'viewer']);
    }

    public function isOwner($orgId)
    {
        if ($this->organization_id != $orgId) {
            return false;
        }

        return $this->role === 'owner';
    }

    public function notify($notification)
    {
        if (in_array('mail', $notification->via($this))) {
            $this->logOutgoingMailNotification($notification);
        }

        return $this->notifiableNotify($notification);
    }

    private function logOutgoingMailNotification($notification)
    {
        $class = join('', array_slice(explode('\\', get_class($notification)), -1));
        $subject = trim(join(' ', preg_split('/(?=[A-Z])/',$class)));

        $mail = new OutgoingMail;
        $mail->to = $this->email();
        $mail->from = config('mail.from.address');
        $mail->subject = $subject;
        $mail->check_in_id = 0;
        $mail->type = $class;
        $mail->save();
    }

    /**
     * A user belongs to groups
     *
     */
    public function groups()
    {
        return $this->belongsToMany('TenFour\Models\Group', 'group_users');
    }

    public function findForPassport($username) {
        list($subdomain, $email) = explode(":", $username);

        $user_id = $this
            ->leftJoin('organizations', 'organizations.id', '=', 'users.organization_id')
            ->where('organizations.subdomain', '=', $subdomain)
            ->whereHas('contacts', function ($query) use ($email) {
                $query
                ->where('contact', '=', $email)
                ->where('type', '=', 'email');
            })->pluck('users.id');

        if (!count($user_id)) {
            return null;
        }

        return $this->where('id', $user_id)->first();
    }

}
