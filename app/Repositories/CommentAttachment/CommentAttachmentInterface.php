<?php

namespace App\Repositories\CommentAttachment;

interface CommentAttachmentInterface
{
    public function addAttachments($attachments, $comment_id);
    public function removeAttachments($delete_attachments, $comment_id);
    public function getAttachmentsById($attachments_id);  
}