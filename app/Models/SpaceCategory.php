<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpaceCategory extends Model
{
    protected $fillable = ['space_id','name','logo'];

    public function getSpaceIdAttribute($value)
    {
		return (string) $value;
	}

    public function posts()
    {
        return $this->hasMany('App\Models\Post','space_category_id','id');
    }

    public function likes()
    {
        return $this->hasManyThrough('App\Models\EndorsePost','App\Models\Post');
    }
}
