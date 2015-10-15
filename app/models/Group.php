<?php

class Group extends Eloquent {
    
    public $table = 'groups';
    protected $relationships = ['users','organization'];
    protected $guarded = ['id', 'created_at', 'updated_at'];

    // relationships
    public function groups() 
    {
        return $this->belongsTo('organization');
    }

    public function users()
    {
        return belongsToMany('group_users');
    }

}
