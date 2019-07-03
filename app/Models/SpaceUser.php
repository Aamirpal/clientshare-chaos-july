<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpaceUser extends Model
{
    protected $casts = [
      'metadata' => 'json',
      'profile_image' => 'json',
      'circular_profile_image' => 'json'
    ];
    public function scopeSpace($query, $space_id)
    {
      return $query->where('space_id', $space_id);
    }

    public function scopeActive($query)
    {
      return $query->where('user_status', '0')
        ->whereNull('deleted_at')
        ->whereRaw("metadata->>'user_profile' !=''");
    }
    public function scopeActiveOrInvited($query)
    {
      return $query->whereNull('deleted_at');
    }
    
    public function scopeMySpace($query, $user_id) 
    {
      return $query->where('user_id', $user_id);
    }

    public function scopeUsersByCompany($query, $space_id, $company_id)
    {
      return $query->where('user_company_id', $company_id)->where('space_id', $space_id);
    }

    public function user()
    {
      return $this->belongsTo("App\User", "user_id","id"); 
    }

    public function share(){
      return $this->belongsTo("App\Space", "space_id"); 
    }
    
    public function company(){        
      return $this->belongsTo("App\Company", "user_company_id", "id");
    }

    public function subCompany(){        
      return $this->belongsTo("App\Company", "sub_company_id", "id");
    }

    public function getSubCompanyIdAttribute($sub_company_id) {
      return $sub_company_id == config('constants.DUMMY_UUID')[0] ? null : $sub_company_id;
    }
    
    public function userCompany(){
      $relation = !$this->sub_company_id ? 'user_company_id' : 'sub_company_id';
      return $this->belongsTo("App\Company", $relation, "id");
    }
    
    public function getCompanyIdAttribute(){
     return in_array($this->sub_company_id, config('constants.DUMMY_UUID')) ? $this->user_company_id : $this->sub_company_id;
   }

    public function userRole() {
        return $this->belongsTo("App\UserType", "user_type_id");
    }

    public static function UserSpaces($user_id, $selection_method){
      return static::where('user_id', $user_id)->active()->$selection_method();
    }

}
