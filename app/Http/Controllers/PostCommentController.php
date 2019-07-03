<?php

namespace App\Http\Controllers;

use Auth;
use App\{Comment, CommentAttachment, Post};
use App\Jobs\{SendCommentAlert, SendCommentEditAlert};
use Illuminate\Http\Request;
use App\Helpers\Logger;

class PostCommentController extends Controller {
    public function index(Request $request) {
        $post = Post::getPostComments($request->space_id, $request->post_id);
        $comment_total = Comment::postComments($request->space_id, $request->post_id);
        $total_comments = sizeOfCustom($comment_total);
        $view_more = (isset($request->view_more) && $request->view_more) ? true : false;
    	return view('posts/comment_section', ['view_more'=>$view_more, 'profile_img'=>Auth::user()->profile_image_url,'posts'=>$post[0], 'space_id' => $request->space_id]);
    }

    public function create(){}

    public function store(Request $request){

        $comment = Comment::addComment($request->all());
        $attachments = CommentAttachment::addAttachments($request->attachments,$comment->id);

        dispatch(new SendCommentAlert([
            'comment' => $comment,
            'current_user' => Auth::user()
        ]));

        return $comment;
    }

    public function show($id){
        $comment = Comment::with('attachments')->findOrFail($id);
        $comment['comment'] = formatCommentText($comment['comment'])['raw_comment'];
        return $comment;
    }

    public function edit($id){}

    public function update(Request $request, $comment_id){

        (new Logger)->log([
            'action' => 'update comment',
            'description' => 'update comment'
        ]);

        if (!empty($request->comment['comment_text'])){

            if(isset($request->comment['comment_attachment'])){
                $attachments = CommentAttachment::where('comment_id', $comment_id)->get()->toArray();

                $comment_attachment = array_filter($request->comment['comment_attachment']);
                $delete_attachments = array_column($comment_attachment, 'uid');

                CommentAttachment::removeAttachments($comment_id, $delete_attachments);

                foreach ($attachments as $attachment) {
                    array_walk($comment_attachment, function($new_attachment, $key)use($attachment, &$comment_attachment){
                        if($new_attachment['uid'] == $attachment['metadata']['uid']){
                            unset($comment_attachment[$key]);
                        }
                    });
                }
                $attachments = CommentAttachment::addAttachments($comment_attachment, $comment_id);
            } else CommentAttachment::removeAttachments($comment_id, []);
            (new Comment)->updateComment($comment_id, ['comment' => trim($request->comment['comment_text'])]);

            dispatch(new SendCommentEditAlert([
                'comment_id' => $comment_id,
                'current_user' => Auth::user()
            ]));
        }

        $comment = formatCommentText($request->comment['comment_text']);
        return ['comment'=>$comment, 'show_more_less' => checkSeeMoreEligiblity($comment['comment_after_process'])];
    }

    public function destroy($id){}
}
