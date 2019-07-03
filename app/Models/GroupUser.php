<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class GroupUser extends Model
{
    protected $fillable = ['user_id','group_id','status'];

    public function scopeActive($query)
    {
      return $query->where('status', '1');
    }

    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }
}
