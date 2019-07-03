<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model {
    
    public function getIdAttribute($value){
    	return (string) $value;
    }
    public static function getEmailLogById($id) {
        if(!strlen(($id))) {
            return [null];
		}
        return static::where('id', $id)->first();
    }
    public static function getEmailLogByStatus($status = 1, $limit = 100) {
        if(!strlen(($status))) {
            return [null];
		}
        return static::where('status', $status)->take($limit)->get()->toArray();
    }

    public function updateEmailLogStatus($id) {
        return $this->where('id', $id)->update(['status' => config('constants.USER_ROLE_ID')]);
    }
}
