<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ManagementInformationEmailLog extends Model{

    protected $fillable = [
        'space_id',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'json'
    ];

    public static function MIEmailLogs(){
        return static::get()->toArray();
    }

    public function Space(){
        return $this->belongsTo('App\Space');
    }
}
