<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notification;
use App\User;
use App\Space;
use App\{SpaceUser, PostMedia, Post};
use Mail;
use App\Helpers\Post as PostHelper;

class SendEmailsOnPostSharing implements ShouldQueue {

    use InteractsWithQueue,
        Queueable;

    // SerializesModels;

    protected $job_data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($job_data) {
        $this->job_data = $job_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        ini_set('memory_limit', '-1');
        $post_media = PostMedia::where('post_id', $this->job_data['post']['id'])->get()->toArray();
        $post_media = $this->attachmentsLinkWrapping($post_media);
        $see_more_image = '';
        $video_ss = $this->getVideoScreenShot($post_media);

        foreach ($this->job_data['receiver_ids'] as $receiver_id) {

            if(!SpaceUser::getActiveSpaceUser($this->job_data['share'], $receiver_id, 'count'))
                continue;

            if ($receiver_id != $this->job_data['logged_in_user_id']) {
                $notification = new Notification;
                $notification->post_id = $this->job_data['post']['id'];
                $notification->user_id = $receiver_id;
                $notification->space_id = $this->job_data['share'];
                $notification->notification_type = 'post';
                $notification->last_modified_by = $notification->from_user_id = $this->job_data['logged_in_user_id'];
                $notification->comment_count = 0;
                $notification->save();
                if ($notification->id) {
                    $post_data = [];
                    $post_data['mail']['post_subject'] = $this->job_data['post']['subject'];
                    $post_data['mail']['post_link'] = "/singlepost/" . $notification->id . "/" . $this->job_data['post']['id'];

                    $post_data['from_user'] = User::where('id', $this->job_data['logged_in_user_id'])
                    ->with(['SpaceUser' => function($q) use($notification){
                        $q->where('space_id', $notification->space_id);
                    }])->first()->toArray();

                    $spacename = Space::spaceById($this->job_data['share'],'get');

                    $post_data['mail']['space_name'] = $spacename[0]['share_name'];
                    $post_data['mail']['post_body'] = $this->job_data['post']['description'];
                    $post_data['mail']['seller_logo'] = $spacename[0]['seller_circular_logo'];
                    $post_data['mail']['buyer_logo'] = $spacename[0]['buyer_circular_logo'];
                    $post_data['recevier_space'] = SpaceUser::getOneSpaceUserInfo($this->job_data['share'], $receiver_id)->toArray();
                    
                    if ($post_data['recevier_space']['post_alert']) {
                        $post = Post::find($this->job_data['post']['id']);
                        if(empty($post)){
                            return false;
                        }
                        $email_data = [
                            'post' => $post->toArray(),
                            'video_ss' => $video_ss,
                            'see_more_image' => $see_more_image,
                            'post_media' => $post_media,
                            'current_space' => $this->job_data['current_space'],
                            'share'=> $this->job_data['share'],
                            'user_id' => $receiver_id,
                            'post_id' => $this->job_data['post']['id'],
                            'notification_id' => $notification->id,
                            'view' => 'posts.add_post.index',
                            'logged_in_user_id'=>$this->job_data['logged_in_user_id'],
                            'data' => $post_data,
                        ];



                        $email_data['post']['access_user_list'] = explode(',', $email_data['post']['visibility']);
                        // @reviewer: please ignore this loop. This will be addressed while optimizing
                        foreach ($email_data['post']['access_user_list'] as $key => $value) {
                            if(!checkUuidFormat($email_data['post']['access_user_list'][$key])) {
                               unset($email_data['post']['access_user_list'][$key]);
                            }
                        }
                        $email_data['post']['access_user_list'] = (new User)->getProfileImages($email_data['post']['access_user_list']);
                        $this->sendEmail($email_data);
                    }
                }
            }
        }
        return true;
    }

    private function sendEmail($mail_data) {
        $user = User::find($mail_data['user_id']);
        $logged_in_user = User::find($mail_data['logged_in_user_id']);
        $data = $mail_data['data'];
        if ($user) {
            $data['app_url'] = env('APP_URL');
            $data['added_by'] = $user->first_name . ' ' . $user->last_name;
            $data['post_page'] = $data['app_url'] . "/clientshare/" . $mail_data['share'] . "/" . $mail_data['post_id'] . "/" . $mail_data['notification_id'] . "?email=" . base64_encode($user->email) . '&alert=true&via_email=1';
            $data['like_link'] = $data['app_url'] . "/clientshare/" . $mail_data['share'] . "/" . $mail_data['post_id'] . "/" . $mail_data['notification_id'] . "?email=" . base64_encode($user->email) . '&alert=true&via_email=1&like=1';
            $data['link_share'] = $data['app_url'] . "/clientshare/" . $mail_data['share'] . "?email=" . base64_encode($user->email) . '&alert=true&via_email=1';
            $data['unsubscribe_share'] = $data['app_url'] . "/setting/" . $mail_data['share'] . "?email=".base64_encode($user->email). '&alert=true&via_email=1&notification=1&tab_name=notifications-tab';
            $data['path'] = url('/', [], true);
            $subject_new = $data['mail']['post_subject'];
            $sender_name = $data['from_user']['first_name'].' '.$data['from_user']['last_name'];
            $data['post_media'] = $mail_data['post_media'];
            $data['see_more_image'] = $mail_data['see_more_image'];
            $data['video_ss'] = $mail_data['video_ss'];
            $data['post'] = $mail_data['post'];
            $data['post']['metadata'] = json_decode($data['post']['metadata'], true);
            $data['mail']['seller_logo'] = composeEmailUrl($data['mail']['seller_logo']);
            $data['mail']['buyer_logo'] = composeEmailUrl($data['mail']['buyer_logo']);
            
            Mail::send($mail_data['view'], ['mail_data' => $data], function ($message) use ($user,$logged_in_user,$subject_new,$mail_data,$sender_name) {
                $message->from(env("SENDER_FROM_EMAIL"), "$sender_name");
                $message->to($user->email);
                $message->subject($subject_new);
                $message->replyTo($logged_in_user->email ?? env("SENDER_FROM_EMAIL"));
                $message->getSwiftMessage()->getHeaders()->addTextHeader('user_id', $mail_data['user_id']);
                $message->getSwiftMessage()->getHeaders()->addTextHeader('post_id', $mail_data['post_id']);
                $message->getSwiftMessage()->getHeaders()->addTextHeader('space_id', $mail_data['share']);
                $message->getSwiftMessage()->getHeaders()->addTextHeader('X-PM-Tag', 'post-alert');

            });
            return response()->json(['message' => 'Request completed']);
        }
    }

    private function getVideoScreenShot($post_media){
        $video = anyVideo($post_media, true);
        if($video){
            $url = getAwsSignedURL($post_media[$video-1]['metadata']['url']);
            $image = createAndUploadVideoScreentshot($url);
            $dimensions = generateImageThumbnail($image, 380, 290, false, true);
            return composeEmailURL((new PostHelper)->mergeImage($image, composeUrl('/fade-layer-60.png', false), $dimensions));
        }
    }

    private function attachmentsLinkWrapping($attachments){
        foreach ($attachments as $key => $attachment) {
            $attachment['s3_file_path'] = composeEmailURL($attachment['s3_file_path']);
            $attachments[$key] = $attachment;
        }
        return $attachments;
    }

}