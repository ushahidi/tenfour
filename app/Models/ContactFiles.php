<?php

namespace TenFour\Models;

use Illuminate\Database\Eloquent\Model;

class ContactFiles extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contact_files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'organization_id', 'filename', 'size', 'mime', 'columns','maps_to'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'columns'  => 'array',
        'maps_to' => 'array'
    ];

    protected $hidden = ['size', 'mime'];
}
