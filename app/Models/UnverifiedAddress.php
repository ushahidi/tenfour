<?php

namespace TenFour\Models;

use Illuminate\Database\Eloquent\Model;

class UnverifiedAddress extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
	protected $table = 'unverified_addresses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['address', 'verification_token', 'type', 'code', 'code_attempts'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['verification_token', 'code'];
}
