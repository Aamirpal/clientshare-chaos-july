<?php

namespace App\Http\Controllers\v2;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Comment\CommentInterface;
use App\Repositories\RemoveCloudFile\RemoveCloudFileInterface;
use App\Repositories\CommentAttachment\CommentAttachmentInterface;
use App\Repositories\GroupUser\GroupUserInterface;
use App\Jobs\v2\SendCommentAlert;
use App\Traits\v2\Comment as CommentTrait;

class CommentController extends Controller
{
    use CommentTrait;
    private $comment, $comment_attachment, $remove_cloud_files, $group_users;
	
    public function __construct(
        CommentInterface $comment, CommentAttachmentInterface $comment_attachment,
        RemoveCloudFileInterface $remove_cloud_files, GroupUserInterface $group_users
    ){
        $this->comment = $comment;
        $this->comment_attachment = $comment_attachment;
        $this->remove_cloud_files = $remove_cloud_files;
        $this->group_users = $group_users;
    }

    public function store(Request $request)
    {
        $comment = $this->comment->addComment($request->all());
        $attachments = $this->comment_attachment->addAttachments($request->attachments, $comment->id);

        dispatch(new SendCommentAlert([
            'comment' => $comment,
            'current_user' => Auth::user()
        ], $this->group_users));
        return apiResponse($this->comment->getComment($comment['id']));
    }

    public function update(Request $request, $comment_id)
    {
        return $this->updateComment($request, $comment_id);
    }

    public function delete($comment_id)
    {
        return $this->deleteComment($comment_id);
    }
}