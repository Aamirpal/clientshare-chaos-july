<?php

namespace App\Repositories\CommentAttachment;

use Carbon\Carbon;
use App\Repositories\CommentAttachment\CommentAttachmentInterface;
use App\Models\CommentAttachment;

class CommentAttachmentRepository implements CommentAttachmentInterface {

    protected $comment_attachment;

    public function __construct(CommentAttachment $comment_attachment) 
    {
        $this->comment_attachment = $comment_attachment;
    }

    public function getAttachmentsById($attachments_id)
	{
		return $this->comment_attachment->whereIn('id', $attachments_id)->get()->toArray();
	}

    public function removeAttachments($delete_attachments, $comment_id)
    {
    	return $this->comment_attachment->whereIn('id', $delete_attachments)->delete();
    }

    public function addAttachments($attachments, $comment_id)
    {
        if(!empty($attachments)){
			foreach(array_filter($attachments) as $attachment){
				$count = $this->comment_attachment->where('comment_id', $comment_id)->whereRaw("metadata->>'s3_name' = '".$attachment['s3_name']."' " )->count();
				if($count) continue;
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
				$this->comment_attachment->create($row);
			}
	    }
    }
}