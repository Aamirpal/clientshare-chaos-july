<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OTP extends Model {
    protected $keyType = 'string';
	protected $table = "otp";
    
    protected $casts = [
        'metadata' => 'json'
    ];

    protected $fillable = [
        'app_url','metadata','called','method'
    ];

	public function getIdAttribute($value) {
        return (string) $value;
    }

    public static function searchByUrl($url){
        return static::where('app_url', $url)
            ->whereRaw("metadata->'file_path' is not null")
            ->first();
    }
}