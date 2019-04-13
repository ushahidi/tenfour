<?php

namespace TenFour\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CheckIn extends Model
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
    protected $table = 'check_ins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['message', 'organization_id', 'user_id', 'answers', 'send_via', 'self_test_check_in', 'everyone', 'template'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['recipients', 'replies', 'user', 'organization', 'groups', 'users'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['pivot'];

    public function scheduledCheckIn() {
        return $this->hasOne('TenFour\Models\ScheduledCheckIn', 'check_ins_id', 'id');
    }

    /**
     *
     * Check-ins belong to an organization
     */
    public function organization()
    {
        return $this->belongsTo('TenFour\Models\Organization');
    }

    /**
     * Check-ins belong to a user
     *
     */
    public function user()
    {
        return $this->belongsTo('TenFour\Models\User');
    }

    /**
     *
     * Check-ins have been sent to these users (combination of check-in users and/or groups and/or everyone)
     */
    public function recipients()
    {
        return $this->belongsToMany('TenFour\Models\User', 'check_in_recipients')->withPivot('response_status', 'reply_token');
    }

    /**
     *
     * Check-in users (for templates - check-in may not have been sent to these users yet)
     */
    public function users()
    {
        return $this->belongsToMany('TenFour\Models\User', 'check_in_users');
    }

    /**
     *
     * Check-in groups (for templates)
     */
    public function groups()
    {
        return $this->belongsToMany('TenFour\Models\Group', 'check_in_groups');
    }

    /**
     *
     * Check-ins sent to contacts
     */
    public function messages()
    {
        return $this->belongsToMany('TenFour\Models\Contact', 'check_in_messages')->withTimestamps()->withPivot('from', 'to', 'channel', 'credits', 'credit_adjustment_id');
    }

    /**
     * Replies received to check-in
     */
    public function replies()
    {
        return $this->hasMany('TenFour\Models\Reply');
    }

    public function routeNotificationForSlack()
    {
        return $this->_slack_webhook_url;
    }
}
