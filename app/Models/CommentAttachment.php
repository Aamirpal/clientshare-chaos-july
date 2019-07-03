<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CommentAttachment extends Model 
{
    protected $keyType = 'string';
	protected $appends = ['file_url'];
    protected $fillable = ['file_url_old', 's3_file_path','comment_id','metadata','mime_type','file_name'];
    protected $casts = [
        'metadata' => 'json'
    ];

	public function getIdAttribute($value)
	{
    	return (string) $value;
    }

    public function getFileUrlAttribute()
    {
        return wrapUrl(composeUrl($this->s3_file_path));
    }
}
