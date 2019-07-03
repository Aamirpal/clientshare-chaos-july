<?php

namespace App\Repositories\Comment;

interface CommentInterface
{
    public function addComment($comment_data);
    public function getComment($comment_id);
 	public function updateComment($comment_id, $comment_data);
 	public function deleteComment($comment_id);
}