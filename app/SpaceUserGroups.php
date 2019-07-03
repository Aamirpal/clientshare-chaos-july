<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SpaceUserGroups extends Model
{
    protected $fillable = [
        'space_user_id', 'group_id'
    ];
    public function SpaceUser(){
    	return $this->belongsTo("App\SpaceUser",'space_user_id','id');
    }
}
