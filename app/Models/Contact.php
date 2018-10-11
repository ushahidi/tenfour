<?php

namespace TenFour\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use App;

class Contact extends Model
{
    use Notifiable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
	protected $table = 'contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type', 'preferred', 'user_id', 'organization_id', 'contact', 'passed_self_test', 'unsubscribe_token', 'blocked', 'meta', 'bounce_count'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'meta' => 'json'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['pivot'];

    /**
     * Messages sent to contact
     */
    public function checkIns()
    {
        return $this->belongsToMany('TenFour\Models\CheckIn', 'check_in_messages');
    }

    /**
     * Get the user that owns the contact.
     */
    public function user()
    {
        return $this->belongsTo('TenFour\Models\User');
    }


    public function replies()
    {
        return $this->hasMany('TenFour\Models\Reply');
    }

    public function isValid()
    {
        if ($this->type === 'email') {
            return (new EmailValidator())->isValid($this->contact, new RFCValidation());
        }
        else if ($this->type === 'phone') {
            try {
                $to = App::make('TenFour\Messaging\PhoneNumberAdapter');
                $to->setRawNumber($this->contact);
            } catch (NumberParseException $exception) {
                \Log::warning('Invalid phone number: ' . $this->contact);
                return false;
            }

            return true;
        }

        return true;
    }

    public function canReceiveCheckIn()
    {
        return $this->isValid()
            && !$this->blocked
            && $this->bounce_count < config('tenfour.messaging.bounce_threshold');
    }
}
