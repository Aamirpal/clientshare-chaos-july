<?php

namespace App;
use Session;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model {
    protected $keyType = 'string';
	protected $casts = [
        'metadata' => 'json'
    ];

    protected $fillable = [
        'share_id','email','first_name','last_name','user_id', 'user_type_id'
    ];
    public function getIdAttribute($value) {
      return (string) $value;
    }
    public static function saveInvitedUser($input){
       return Invitation::firstOrCreate([
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email'     => $input['email'],
            'share_id'  => $input['share_id'],
            'user_id'   => $input['user_id'],
            'user_type_id' => $input['user_type_id']??UserType::USER_TYPE['user']
        ]);
    }  
}
