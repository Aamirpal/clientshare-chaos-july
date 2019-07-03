<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class Post extends Model
{
    use SoftDeletes;
    
    protected $guarded = ['id','created_at','updated_at'];
    protected $keyType = 'string';
    protected $casts = [
      'metadata' => 'json',
      'url_preview' => 'json'
    ];

    protected $hidden = [
        'visibility', 'post_file_url', 'remember_token',
        'metadata', 'comment_count', 'minimized_by',
        'reposted_at', 'endorse_count'
    ];

    public function getIdAttribute($value)
    {
		return (string) $value;
    }

    public function group()
    {
        return $this->belongsTo('App\Models\Group');
    }
    
    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id');
    }

    public function postMedia()
    {
        return $this->hasMany('App\Models\PostMedia');
    }

    public function categoryName()
    {
        return $this->hasOne('App\Models\SpaceCategory','id', 'space_category_id');
    }

    public function images() 
    {
        return $this->hasMany('App\PostMedia')->whereRaw("metadata->>'mimeType' ilike '%image%'");
    }

    public function videos() 
    {
        return $this->hasMany('App\PostMedia')->whereRaw("metadata->>'mimeType' ilike '%video%'");
    }

    public function documents() 
    {
        return $this->hasMany('App\PostMedia')->whereRaw("metadata->>'mimeType' not ilike '%video%' and metadata->>'mimeType' not ilike '%image%'");
    }

    public function endorse()
    {
        return $this->hasMany('App\EndorsePost');
    }

    public function endorseByMe()
    {
        return $this->hasMany('App\EndorsePost')->where('user_id', \Auth::user()->id);
    }

    public function postView()
    {
        return $this->hasMany('App\PostViews','post_id');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function scopeHasPostAccess($query, $user_id)
    {
        return $query->whereHas('group', function($group){
            $group->where('name', 'Everyone');
        })
        ->orWhereHas('group.groupUsers', function($group_users) use ($user_id){
            $group_users->where('user_id', $user_id);
        });
    }
}
