<?php

class Organization extends Eloquent {
    
    public $table = 'organizations';
    protected $relationships = ['groups'];
    protected $guarded = ['id', 'created_at', 'updated_at'];

    // relationships
    public function groups() 
    {
        return $this->belongsTo('organization_groups');
    }
}
