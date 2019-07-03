<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BusinessReviewMedia;
use Carbon\Carbon;

class BusinessReview extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $hidden = ['created_at', 'updated_at'];

    public function group() {
        return $this->belongsTo('App\Models\Group', 'group_id');
    }
    public function groupUser() {
        return $this->hasMany('App\Models\GroupUser', 'group_id');
    }

    public function user() {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function businessReviewMedia() {
        return $this->hasMany(BusinessReviewMedia::class, 'business_review_id', 'id');
    }
    public function attendee() {
        return $this->hasMany(Attendee::class, 'business_review_id', 'id');
    }

    public function getReviewDateAttribute($review_date) {
        return Carbon::parse($review_date)->format('d M Y');
    }

    public function images() {
        return $this->hasMany(BusinessReviewMedia::class)->whereRaw("metadata->>'mimeType' ilike '%image%'");
    }

    public function videos() {
        return $this->hasMany(BusinessReviewMedia::class)->whereRaw("metadata->>'mimeType' ilike '%video%'");
    }

    public function documents() {
        return $this->hasMany(BusinessReviewMedia::class)->whereRaw("metadata->>'mimeType' not ilike '%video%' and metadata->>'mimeType' not ilike '%image%'");
    }

}
