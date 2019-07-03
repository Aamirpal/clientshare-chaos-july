<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\GroupUser;

class Group extends Model
{
    protected $fillable = ['name', 'space_id', 'is_default'];

    public function groupUsers(){
        return $this->hasMany(GroupUser::class,'group_id','id');
    }

}
