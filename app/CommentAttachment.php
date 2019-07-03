<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CommentAttachment extends Model {

	protected $keyType = 'string';
    protected $appends = ['file_url'];
    protected $fillable = ['file_url_old', 's3_file_path', 'comment_id', 'metadata', 'mime_type', 'file_name'];
    protected $casts = [
        'metadata' => 'json'
    ];


    public static function removeAttachments($comment_id, $delete_attachments){
    	return static::where('comment_id', $comment_id)
            ->whereRaw("metadata->>'uid' not in ('".implode("','",$delete_attachments)."')")->delete();
    }
	public static function addAttachments($attachments, $comment_id){
		if(!empty($attachments)){
			foreach(array_filter($attachments) as $attachment){
				$extension = substr($attachment['originalName'], strrpos($attachment['originalName'], '.') + 1);
				if(strtolower($extension) == 'mov'){
				    $attachment['mimeType'] = 'video/mp4';
				    $attachment['url'] = str_replace($extension,"mp4",$attachment['url']);
			    }
				$row = [
					's3_file_path' => filePathUrlToJson($attachment['url']),
                    'file_url_old' => $attachment['url'],
					'comment_id' => $comment_id,
					'metadata' => $attachment,
					'mime_type' => $attachment['mimeType'],
					'file_name' => $attachment['originalName'],
					'created_at' => Carbon::now(),
					'updated_at' => Carbon::now(),
				];
				static::create($row);
			}

			$comment = Comment::find($comment_id);
			PostActivity::create([
		        'post_id' => $comment['post_id'],
		        'user_id' => $comment['user_id'],
		        'metadata' => ['action' => 'add_comment']
		    ]);
	    }
	}

	public function getIdAttribute($value){
    	return (string) $value;
    }

    public function getFileUrlAttribute(){
        return filePathJsonToUrl($this->s3_file_path);
    }

    public static function commentFile($file_name, $selection_method='first'){
      return CommentAttachment::whereRaw("s3_file_path->>'file' ilike '%".$file_name."'")->$selection_method();;
	}

}
