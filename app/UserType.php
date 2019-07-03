<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserType extends Model {
    const ROLES = ['admin'=>'admin', 'user'=>'user'];
    const USER_TYPE = ['super_admin'=>1, 'admin'=>2, 'user'=>3];
	public static function userTypeNameById($type_id){
    	return static::where('id', $type_id)->value('user_type_name');
    }

    public static function userTypeIdByName($type_name){
    	return static::where('user_type_name', $type_name)->value('id');
    }
}
