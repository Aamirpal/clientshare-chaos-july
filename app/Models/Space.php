<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Space extends Model
{

    protected $keyType = 'string';

    protected $fillable = [
        'user_id', 'company_id', 'share_name', 'category_tags', 'allow_feedback', 'metadata', 'company_seller_id',
        'company_buyer_id', 'company_seller_logo', 'company_buyer_logo', 'seller_processed_logo', 'buyer_processed_logo',
        'background_logo', 'domain_restriction', 'doj', 'sub_companies', 'seller_logo', 'buyer_logo', 'processed_seller_logo',
        'processed_buyer_logo', 'email_header_image', 'email_header', 'background_image', 'allowed_ip', 'ip_restriction',
        'seller_circular_logo', 'buyer_circular_logo', 'version', 'share_profile_progress', 'invite_admin_status'
    ];
    protected $casts = [
        'metadata' => 'json',
        'seller_logo' => 'json',
        'buyer_logo' => 'json',
    ];

    public function getIdAttribute($value) {
		return (string) $value;
	}

	public function seller()
	{
        return $this->hasOne("App\Company", "id","company_seller_id"); 
    }

    public function buyer()
    {
        return $this->hasOne("App\Company", "id","company_buyer_id"); 
    }

    public function spaceCategories()
    {
        return $this->hasMany('App\Models\SpaceCategory','space_id','id');
    }

    public function QuickLinks(){
        return $this->hasMany("App\QuickLinks", "share_id", "id");
    }

    public function Posts(){
        return $this->hasMany("App\Models\Post", "space_id", "id");
    }

    public function spaceMember(){
        return $this->hasOne("App\Models\SpaceUser", "space_id")->where('user_type_id', config('constants.USER_TYPE_ID'));
    }

    public function spaceAdmin(){
        return $this->hasOne("App\Models\SpaceUser", "space_id")->where('user_type_id', config('constants.USER_ROLE_ID'));
    }
}
