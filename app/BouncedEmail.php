<?php

namespace App;
use Session;

use Illuminate\Database\Eloquent\Model;

class BouncedEmail extends Model {
	protected $casts = [
        'metadata' => 'json'
    ];

    protected $fillable = [
        'share_id','from_email','to_email','metadata'
    ];

    public static function bounceEmails($space_id, $date_range, $invitation_tags,$tags_flag){
        $query = static::bounceList($space_id)->dateRange($date_range);
        if($tags_flag) $query->whereRaw("metadata->>'Tag' in ('".implode("','", $invitation_tags)."')");
        else $query->whereRaw("( metadata->>'Tag' is null or metadata->>'Tag' not in ('".implode("','", $invitation_tags)."'))");
        return $query->get();
    }


    public function scopeBounceList( $query, $share_id ){
    	return $query->where('share_id', $share_id);
    }

    
    public function scopeDateRange($query, $date_range) {
    	return $query->whereRaw("created_at between  '".$date_range['start_date']."' and  '".$date_range['end_date']."'");
    }
}
