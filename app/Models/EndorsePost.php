<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EndorsePost extends Model
{

	protected $keyType = 'string';
	
    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id');
    }
}