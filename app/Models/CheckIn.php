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
    protected $fillable = ['message', 'organization_id', 'user_id', 'answers', 'send_via', 'self_test_check_in'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['recipients', 'replies', 'user', 'organization'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['pivot'];

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
     * Check-ins belong to user
     */
    public function recipients()
    {
        return $this->belongsToMany('TenFour\Models\User', 'check_in_recipients')->withPivot('response_status', 'reply_token');
    }

    /**
     *
     * Check-ins sent to contacts
     */
    public function messages()
    {
        return $this->belongsToMany('TenFour\Models\Contact', 'check_in_messages')->withTimestamps()->withPivot('from');
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
