<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model {

	protected $keyType = 'string';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function getIdAttribute($value) {
        return (string) $value;
    }

}
