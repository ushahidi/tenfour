<?php

namespace TenFour\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'organization_id', 'user_id', 'description', 'profile_picture'];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'meta' => 'json'
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['organization', 'members'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['pivot'];

    /**
     *
     * Groups belong to an organization
     */
    public function organization()
    {
        return $this->belongsTo('TenFour\Models\Organization');
    }

    /**
     *
     * A group has users
     */
    public function members()
    {
        return $this->belongsToMany('TenFour\Models\User', 'group_users');
    }

}
