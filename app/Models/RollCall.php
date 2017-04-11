<?php

namespace RollCall\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class RollCall extends Model
{
    use Notifiable;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'answers'  => 'array',
        'send_via' => 'json'
    ];
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
    protected $fillable = ['message', 'organization_id', 'user_id', 'answers', 'send_via', 'self_test_roll_call'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['recipients', 'replies', 'user'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['pivot'];

    /**
     *
     * Roll calls belong to an organization
     */
    public function organization()
    {
        return $this->belongsTo('RollCall\Models\Organization');
    }

    /**
     * Roll calls belong to a user
     *
     */
    public function user()
    {
        return $this->belongsTo('RollCall\Models\User');
    }

    /**
     *
     * Roll calls belong to user
     */
    public function recipients()
    {
        return $this->belongsToMany('RollCall\Models\User', 'roll_call_recipients')->withPivot('response_status');
    }

    /**
     *
     * Roll calls sent to contacts
     */
    public function messages()
    {
        return $this->belongsToMany('RollCall\Models\Contact', 'roll_call_messages')->withTimestamps();
    }

    /**
     * Replies received to rollcall
     */
    public function replies()
    {
        return $this->hasMany('RollCall\Models\Reply');
    }

    public function routeNotificationForSlack()
    {
        return $this->_slack_webhook_url;
    }
}
