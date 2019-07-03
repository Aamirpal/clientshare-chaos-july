<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\{
    BusinessReview,
    SpaceUser
};

class Attendee extends Model {

    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $hidden = ['created_at', 'updated_at'];

    public function businessReview() {
        return $this->belongsTo(BusinessReview::class, 'business_review_id');
    }
    public function spaceUser() {
        return $this->belongsTo(SpaceUser::class, 'space_user_id');
    }

    public function groupUser() {
        return $this->hasMany('App\Models\GroupUser', 'group_id');
    }

}
