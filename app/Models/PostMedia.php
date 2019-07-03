<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post;

class PostMedia extends Model
{
    protected $casts = [
        'metadata' => 'json',
        's3_file_path' => 'json'
	];
	protected $fillable = ['post_id', 'metadata', 's3_file_path', 'post_file_url'];
	protected $appends = ['created_at_formatted', 'post_file_url', 'file_extension', 'file_name', 'file_full_name'];
    protected $hidden = ['post_file_url_old'];

    const FILE_FILTERS = [
		'doc' => ['xls', 'csv', 'xlsx', 'docx', 'ppt', 'pptx', 'doc'],
		'pdf' => ['pdf'],
		'img' => ['jpg','jpeg','png'],
		'vid' => ['mp4','mov','MOV'],
		'url' => ['url']
	];

    public function getCreatedAtFormattedAttribute()
    {
		return Carbon::parse($this->created_at)->format('d/m/Y');
	}

	public function getPostFileUrlAttribute()
	{
        return wrapUrl(composeUrl($this->s3_file_path));
    }

    public function getFileExtensionAttribute()
	{
        $file_array = $this->fileToArray();
        return is_array($file_array) ? end($file_array) : '';
    }

    public function getFileNameAttribute()
	{
        $file_array = $this->fileToArray();
        array_pop($file_array);
        return implode('.',$file_array);
    }

    public function getFileFullNameAttribute()
	{
        $meta_data = is_array($this->metadata) ? json_decode(json_encode($this->metadata), true) : 
        json_decode($this->metadata, true);
        return $meta_data['originalName'];
    }
    
    private function fileToArray(){
        $meta_data = is_array($this->metadata) ? json_decode(json_encode($this->metadata), true) : 
        json_decode($this->metadata, true);
        return explode('.', $meta_data['originalName']);
    }

    public function post()
    {
        return $this->belongsTo(Post::class,'post_id','id');
    }
	
}
