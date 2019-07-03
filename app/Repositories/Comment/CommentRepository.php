<?php

namespace App\Repositories\Comment;

use App\Repositories\Comment\CommentInterface;
use App\Models\Comment;

class CommentRepository implements CommentInterface {

    protected $comment;

    public function __construct(Comment $comment) 
    {
        $this->comment = $comment;
    }

    public function addComment($comment_data)
    {
        return $this->comment->create($comment_data);
    }

    public function getComment($comment_id)
    {
        return $this->comment->where('id', $comment_id)->with('attachments')->first()->toArray();
    }

    public function updateComment($comment_id, $comment_data)
    {
        return $this->comment->where('id',  $comment_id)->update($comment_data);
    }

    public function deleteComment($comment_id)
    {
        return $this->comment->where('id',  $comment_id)->delete();
    }
}