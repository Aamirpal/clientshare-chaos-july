<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SpaceGroups extends Model
{
    protected $fillable = [
        'space_id', 'group'
    ];
    public function SpaceUserGroups(){
    	return $this->hasmany("App\SpaceUserGroups",'group_id'); 
    }
    public static function invitedSpaceUserGroups($space_id,$user_id) {
    return static::where('space_id', $space_id)->where('created_by', $user_id)
        ->with(['SpaceUserGroups.SpaceUser' => function($query) {
            $query->where('user_status', '0')->where('metadata->invitation_code', '1');
        }])
        ->with(['SpaceUserGroups.SpaceUser.user'])
        ->orderBy('created_at', 'desc')
        ->get()->toArray();
    }
}
