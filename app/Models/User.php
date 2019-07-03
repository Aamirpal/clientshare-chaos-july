<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetClientSharePassword as ResetPasswordNotification;

class User extends Authenticatable {
 	
  	use Notifiable;

    protected $keyType = 'string';
  	protected $appends = ['fullname', 'circular_image_url'];

	public function getIdAttribute($value) 
	{
    	return (string) $value;
  	}

	public function getFullNameAttribute() 
	{
    	return ucfirst($this->first_name) . " " . ucfirst($this->last_name);
	}

	public function fullName()
	{
		return $this->first_name . ' ' . $this->last_name;
	}

	public function getCircularImageUrlAttribute() {
		if(!$this->profile_image) return '';
		$image = $this->circular_profile_image ?? $this->profile_image;
        $image = is_array($image) ? $image : json_decode($image, true);
        return wrapUrl(composeFilePath($image));
    }
}
