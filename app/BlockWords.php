<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlockWords extends Model
{
    //
    protected $table = 'blocked_words';
    protected $fillable = [
        'id', 'block_words'
    ];

    public static function blockWords($words){
    	return static::where('block_words','ilike','%'.$words.'%')->pluck('block_words');
    }
}
